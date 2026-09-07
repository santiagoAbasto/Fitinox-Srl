<?php

namespace App\Services;

use App\Models\ClientOrder;
use App\Models\IntegrationOutboxEvent;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IntegrationOutbox
{
    public function client(User $user, string $eventType): ?IntegrationOutboxEvent
    {
        if (! $this->available()) return null;

        return $this->capture($eventType, 'client', $user->id, [
            'web_id' => $user->id,
            'cod_cliente' => $user->integrationClient?->external_code,
            'nombre' => $user->name,
            'razon_social' => $user->company,
            'email' => $user->email,
            'telefono' => $user->phone,
            'Nro_documento' => $user->tax_id,
            'estado' => $user->approval_status,
            'activo' => (bool) $user->is_active,
        ]);
    }

    public function order(ClientOrder $order, string $eventType): ?IntegrationOutboxEvent
    {
        if (! $this->available()) return null;

        $order->loadMissing(['user.integrationClient', 'items']);

        return $this->capture($eventType, 'order', $order->number, [
            'numero_web' => $order->number,
            'referencia_sistema' => $order->external_reference,
            'estado' => $order->status,
            'fecha_entrega' => $order->delivery_date?->format('Y-m-d'),
            'cliente' => [
                'web_id' => $order->user_id,
                'cod_cliente' => $order->user?->integrationClient?->external_code,
                'razon_social' => $order->user?->company,
                'email' => $order->user?->email,
                'Nro_documento' => $order->user?->tax_id,
            ],
            'subtotal' => $order->subtotal,
            'descuento_total' => $order->discount_total,
            'total' => $order->total,
            'notas' => $order->notes,
            'entrega' => [
                'modalidad' => $order->delivery_method,
                'transporte' => $order->transport_name,
                'direccion_transporte' => $order->transport_address,
                'telefono_transporte' => $order->transport_phone,
                'destino' => $order->delivery_destination,
            ],
            'articulos' => $order->items->map(fn ($item) => [
                'cod_articulo' => $item->code,
                'descripcion' => $item->product_name,
                'cantidad' => $item->quantity,
                'precio_lista' => $item->list_price,
                'descuento_porcentaje' => $item->discount_percent,
                'precio_unitario' => $item->unit_price,
                'total_linea' => $item->line_total,
            ])->values()->all(),
        ]);
    }

    public function quote(QuoteRequest $quote, string $eventType = 'quote.created'): ?IntegrationOutboxEvent
    {
        if (! $this->available()) return null;

        $quote->loadMissing('items');

        return $this->capture($eventType, 'quote', $quote->id, [
            'web_id' => $quote->id,
            'nombre' => $quote->name,
            'razon_social' => $quote->company,
            'email' => $quote->email,
            'telefono' => $quote->phone,
            'notas' => $quote->notes,
            'estado' => $quote->status,
            'articulos' => $quote->items->map(fn ($item) => [
                'cod_articulo' => $item->code,
                'descripcion' => $item->product_name,
                'categoria' => $item->category_name,
                'tubo' => $item->tube,
                'rosca' => $item->thread,
                'cantidad' => $item->quantity,
            ])->values()->all(),
        ]);
    }

    private function capture(string $eventType, string $aggregateType, string|int $aggregateId, array $payload): ?IntegrationOutboxEvent
    {
        try {
            if (! Schema::hasTable('integration_outbox_events')) {
                return null;
            }

            return IntegrationOutboxEvent::create([
                'uuid' => (string) Str::uuid(),
                'event_type' => $eventType,
                'aggregate_type' => $aggregateType,
                'aggregate_id' => (string) $aggregateId,
                'payload' => $payload,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function available(): bool
    {
        return Schema::hasTable('integration_outbox_events');
    }
}
