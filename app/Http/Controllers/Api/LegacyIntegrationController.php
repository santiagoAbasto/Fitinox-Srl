<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientOrder;
use App\Models\IntegrationArticle;
use App\Models\IntegrationClient;
use App\Models\IntegrationOutboxEvent;
use App\Models\IntegrationSyncRequest;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LegacyIntegrationController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'service' => 'fitinox-legacy-integration',
            'version' => 'v1',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function articles(Request $request): JsonResponse
    {
        $data = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
        ]);

        $records = IntegrationArticle::query()
            ->where('id', '>', $data['after_id'] ?? 0)
            ->orderBy('id')
            ->limit($data['limit'] ?? 100)
            ->get();

        return response()->json([
            'data' => $records->map(fn (IntegrationArticle $article) => [
                'id' => $article->id,
                'cod_articulo' => $article->external_code,
                'configuracion' => $article->configuration,
                'modelo' => $article->model,
                'TipoArticulo' => $article->article_type,
                'fabricacion' => $article->manufacturing_type,
                'categoria' => $article->source_category,
                'stock' => $article->stock === null ? null : (float) $article->stock,
                'UnMedida' => $article->unit,
                'vinculado_web' => $article->product_variant_id !== null,
                'actualizado_en' => $article->updated_at?->toIso8601String(),
            ])->values(),
            'next_after_id' => $records->last()?->id,
            'has_more' => $records->count() === ($data['limit'] ?? 100),
        ]);
    }

    public function upsertArticles(Request $request): JsonResponse
    {
        $data = $request->validate([
            'generated_at' => ['nullable', 'date'],
            'records' => ['required', 'array', 'min:1', 'max:500'],
            'records.*.cod_articulo' => ['required', 'string', 'max:120'],
            'records.*.configuracion' => ['nullable', 'string', 'max:5000'],
            'records.*.modelo' => ['nullable', 'string', 'max:255'],
            'records.*.TipoArticulo' => ['nullable', 'string', 'max:30'],
            'records.*.fabricacion' => ['nullable', 'string', 'max:30'],
            'records.*.categoria' => ['nullable', 'string', 'max:255'],
            'records.*.stock' => ['nullable', 'numeric', 'between:-999999999,999999999'],
            'records.*.UnMedida' => ['nullable', 'string', 'max:30'],
            'records.*.actualizado_en' => ['nullable', 'date'],
        ]);

        return $this->idempotent($request, 'articles.upsert', function () use ($data) {
            $result = ['processed' => 0, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'stale' => 0, 'linked' => 0, 'unlinked' => 0];

            foreach ($data['records'] as $record) {
                $code = trim($record['cod_articulo']);
                $sourceTimestamp = $this->sourceTimestamp($record, $data);
                $payload = [
                    'cod_articulo' => $code,
                    'configuracion' => $this->nullableText($record['configuracion'] ?? null),
                    'modelo' => $this->nullableText($record['modelo'] ?? null),
                    'TipoArticulo' => $this->nullableText($record['TipoArticulo'] ?? null),
                    'fabricacion' => $this->nullableText($record['fabricacion'] ?? null),
                    'categoria' => $this->nullableText($record['categoria'] ?? null),
                    'stock' => array_key_exists('stock', $record) && $record['stock'] !== null ? round((float) $record['stock'], 3) : null,
                    'UnMedida' => $this->nullableText($record['UnMedida'] ?? null),
                ];
                $hash = $this->payloadHash($payload);
                $article = IntegrationArticle::where('external_code', $code)->first();

                if ($article?->source_updated_at && $sourceTimestamp && $article->source_updated_at->gt($sourceTimestamp)) {
                    $result['processed']++;
                    $result['stale']++;
                    continue;
                }

                $variant = ProductVariant::where('code', $code)->first();
                $attributes = [
                    'product_variant_id' => $variant?->id,
                    'configuration' => $payload['configuracion'],
                    'model' => $payload['modelo'],
                    'article_type' => $payload['TipoArticulo'],
                    'manufacturing_type' => $payload['fabricacion'],
                    'source_category' => $payload['categoria'],
                    'stock' => $payload['stock'],
                    'unit' => $payload['UnMedida'],
                    'source_updated_at' => $sourceTimestamp,
                    'source_hash' => $hash,
                    'source_payload' => $payload,
                    'last_received_at' => now(),
                ];

                if (! $article) {
                    $article = IntegrationArticle::create(['external_code' => $code, ...$attributes]);
                    $result['created']++;
                } elseif ($article->source_hash === $hash && $article->product_variant_id === $variant?->id) {
                    $article->update(['last_received_at' => now()]);
                    $result['unchanged']++;
                } else {
                    $article->update($attributes);
                    $result['updated']++;
                }

                if ($variant) {
                    if ($payload['stock'] !== null) {
                        $variant->update(['physical_stock' => $payload['stock']]);
                    }
                    $result['linked']++;
                } else {
                    $result['unlinked']++;
                }
                $result['processed']++;
            }

            return ['ok' => true, 'resource' => 'articles', ...$result];
        });
    }

    public function clients(Request $request): JsonResponse
    {
        $data = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,500'],
        ]);

        $records = IntegrationClient::query()
            ->where('id', '>', $data['after_id'] ?? 0)
            ->orderBy('id')
            ->limit($data['limit'] ?? 100)
            ->get();

        return response()->json([
            'data' => $records->map(fn (IntegrationClient $client) => [
                'id' => $client->id,
                'cod_cliente' => $client->external_code,
                'razon_social' => $client->business_name,
                'email' => $client->email_raw,
                'emails_validos' => $client->email_candidates ?? [],
                'Nro_documento' => $client->tax_id,
                'vinculado_web' => $client->user_id !== null,
                'actualizado_en' => $client->updated_at?->toIso8601String(),
            ])->values(),
            'next_after_id' => $records->last()?->id,
            'has_more' => $records->count() === ($data['limit'] ?? 100),
        ]);
    }

    public function upsertClients(Request $request): JsonResponse
    {
        $data = $request->validate([
            'generated_at' => ['nullable', 'date'],
            'records' => ['required', 'array', 'min:1', 'max:500'],
            'records.*.cod_cliente' => ['required'],
            'records.*.razon_social' => ['required', 'string', 'max:255'],
            'records.*.email' => ['nullable', 'string', 'max:1000'],
            'records.*.Nro_documento' => ['nullable'],
            'records.*.actualizado_en' => ['nullable', 'date'],
        ]);

        return $this->idempotent($request, 'clients.upsert', function () use ($data) {
            $result = ['processed' => 0, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'stale' => 0, 'linked' => 0, 'unlinked' => 0];
            $portalUsers = User::query()->where('role', 'client')->get(['id', 'email', 'tax_id', 'company']);
            $usersByEmail = $portalUsers->keyBy(fn (User $user) => mb_strtolower($user->email));
            $usersByTaxId = $portalUsers->filter(fn (User $user) => $this->normalizedTaxId($user->tax_id) !== null)
                ->keyBy(fn (User $user) => $this->normalizedTaxId($user->tax_id));

            foreach ($data['records'] as $record) {
                $externalCode = $this->identifier($record['cod_cliente']);
                $businessName = trim($record['razon_social']);
                $emailRaw = $this->nullableText($record['email'] ?? null);
                $emails = $this->extractEmails($emailRaw);
                $taxId = $this->identifier($record['Nro_documento'] ?? null, true);
                $sourceTimestamp = $this->sourceTimestamp($record, $data);
                $payload = [
                    'cod_cliente' => $externalCode,
                    'razon_social' => $businessName,
                    'email' => $emailRaw,
                    'Nro_documento' => $taxId,
                ];
                $hash = $this->payloadHash($payload);
                $client = IntegrationClient::where('external_code', $externalCode)->first();

                if ($client?->source_updated_at && $sourceTimestamp && $client->source_updated_at->gt($sourceTimestamp)) {
                    $result['processed']++;
                    $result['stale']++;
                    continue;
                }

                $user = $client?->user;
                if (! $user) {
                    foreach ($emails as $email) {
                        $user = $usersByEmail->get(mb_strtolower($email));
                        if ($user) break;
                    }
                }
                if (! $user && ($normalizedTaxId = $this->normalizedTaxId($taxId))) {
                    $user = $usersByTaxId->get($normalizedTaxId);
                }

                $attributes = [
                    'user_id' => $user?->id,
                    'business_name' => $businessName,
                    'email_raw' => $emailRaw,
                    'email_candidates' => $emails,
                    'tax_id' => $taxId,
                    'source_updated_at' => $sourceTimestamp,
                    'source_hash' => $hash,
                    'source_payload' => $payload,
                    'last_received_at' => now(),
                ];

                if (! $client) {
                    $client = IntegrationClient::create(['external_code' => $externalCode, ...$attributes]);
                    $result['created']++;
                } elseif ($client->source_hash === $hash && $client->user_id === $user?->id) {
                    $client->update(['last_received_at' => now()]);
                    $result['unchanged']++;
                } else {
                    $client->update($attributes);
                    $result['updated']++;
                }

                if ($user) {
                    $user->forceFill([
                        'company' => $businessName,
                        'tax_id' => $taxId,
                    ])->save();
                    $result['linked']++;
                } else {
                    $result['unlinked']++;
                }
                $result['processed']++;
            }

            return ['ok' => true, 'resource' => 'clients', ...$result];
        });
    }

    public function events(Request $request): JsonResponse
    {
        $data = $request->validate([
            'after_id' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'between:1,200'],
        ]);
        $limit = $data['limit'] ?? 100;
        $events = IntegrationOutboxEvent::query()
            ->whereNull('acknowledged_at')
            ->where('id', '>', $data['after_id'] ?? 0)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $events->map(fn (IntegrationOutboxEvent $event) => [
                'id' => $event->id,
                'uuid' => $event->uuid,
                'type' => $event->event_type,
                'aggregate_type' => $event->aggregate_type,
                'aggregate_id' => $event->aggregate_id,
                'occurred_at' => $event->occurred_at->toIso8601String(),
                'payload' => $event->payload,
            ])->values(),
            'next_after_id' => $events->last()?->id,
            'has_more' => $events->count() === $limit,
        ]);
    }

    public function acknowledgeEvents(Request $request): JsonResponse
    {
        $data = $request->validate([
            'event_uuids' => ['required', 'array', 'min:1', 'max:200'],
            'event_uuids.*' => ['required', 'uuid', 'distinct'],
        ]);

        $updated = IntegrationOutboxEvent::query()
            ->whereIn('uuid', $data['event_uuids'])
            ->whereNull('acknowledged_at')
            ->update(['acknowledged_at' => now(), 'updated_at' => now()]);

        return response()->json(['ok' => true, 'acknowledged' => $updated]);
    }

    public function updateOrder(Request $request, ClientOrder $clientOrder): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['received', 'processing', 'quoted', 'completed', 'cancelled'])],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'delivery_date' => ['nullable', 'date'],
        ]);
        $clientOrder->update([
            ...$data,
            'integration_synced_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'numero_web' => $clientOrder->number,
            'referencia_sistema' => $clientOrder->external_reference,
            'estado' => $clientOrder->status,
            'fecha_entrega' => $clientOrder->delivery_date?->format('Y-m-d'),
            'sincronizado_en' => $clientOrder->integration_synced_at?->toIso8601String(),
        ]);
    }

    private function idempotent(Request $request, string $resource, Closure $operation): JsonResponse
    {
        $key = trim((string) $request->header('Idempotency-Key', ''));
        if ($key === '' || mb_strlen($key) > 160) {
            return response()->json([
                'message' => 'El encabezado Idempotency-Key es obligatorio y admite hasta 160 caracteres.',
                'code' => 'invalid_idempotency_key',
            ], 422);
        }

        $requestHash = hash('sha256', $request->getContent());

        $result = DB::transaction(function () use ($resource, $key, $requestHash, $operation) {
            // La reserva y la operación comparten transacción. Así, ante dos
            // envíos simultáneos el índice único evita que se consoliden efectos
            // duplicados; el segundo envío se revierte por completo.
            $stored = IntegrationSyncRequest::query()
                ->where('resource', $resource)
                ->where('idempotency_key', $key)
                ->lockForUpdate()
                ->first();

            if ($stored) {
                return ['stored' => $stored, 'payload' => null];
            }

            $payload = $operation();
            $stored = IntegrationSyncRequest::create([
                'resource' => $resource,
                'idempotency_key' => $key,
                'request_hash' => $requestHash,
                'http_status' => 200,
                'response_payload' => $payload,
            ]);

            return ['stored' => $stored, 'payload' => $payload];
        }, 3);

        /** @var IntegrationSyncRequest $stored */
        $stored = $result['stored'];
        if ($result['payload'] === null) {
            if (! hash_equals($stored->request_hash, $requestHash)) {
                return response()->json([
                    'message' => 'La misma Idempotency-Key ya fue usada con otro contenido.',
                    'code' => 'idempotency_conflict',
                ], 409);
            }

            return response()->json($stored->response_payload, $stored->http_status)
                ->header('Idempotency-Replayed', 'true');
        }

        return response()->json($result['payload']);
    }

    private function sourceTimestamp(array $record, array $batch): ?Carbon
    {
        $value = $record['actualizado_en'] ?? $batch['generated_at'] ?? null;

        return $value ? Carbon::parse($value) : null;
    }

    private function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
    }

    private function nullableText(mixed $value): ?string
    {
        if ($value === null) return null;
        $value = trim((string) $value);

        return $value === '' || mb_strtoupper($value) === 'NULL' ? null : $value;
    }

    private function identifier(mixed $value, bool $zeroIsNull = false): ?string
    {
        if ($value === null || $value === '') return null;
        if (is_float($value) && floor($value) === $value) $value = number_format($value, 0, '.', '');
        $value = trim((string) $value);
        if ($zeroIsNull && preg_match('/^0+(?:\.0+)?$/', $value)) return null;

        return $value;
    }

    private function normalizedTaxId(?string $value): ?string
    {
        if (! $value) return null;
        $normalized = preg_replace('/\D+/', '', $value);

        return $normalized && trim($normalized, '0') !== '' ? $normalized : null;
    }

    private function extractEmails(?string $value): array
    {
        if (! $value) return [];
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches);

        return array_values(array_unique(array_map('mb_strtolower', array_filter(
            $matches[0] ?? [],
            fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        ))));
    }
}
