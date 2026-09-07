<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class OpenApiDocumentationController extends Controller
{
    public function ui(): View
    {
        return view('integration.swagger');
    }

    public function spec(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Fitinox Legacy Integration API',
                'version' => '1.0.0',
                'description' => 'Integración bidireccional entre la web Fitinox y el sistema de gestión. Todas las operaciones requieren HTTPS y un token Bearer privado.',
                'contact' => ['name' => 'Fitinox'],
            ],
            'servers' => [[
                'url' => rtrim(config('app.url'), '/'),
                'description' => config('app.env') === 'production' ? 'Producción' : 'Entorno local',
            ]],
            'security' => [['bearerAuth' => []]],
            'tags' => [
                ['name' => 'Estado', 'description' => 'Verificación de conectividad.'],
                ['name' => 'Artículos', 'description' => 'Sincronización de artículos y stock desde el sistema de gestión.'],
                ['name' => 'Clientes', 'description' => 'Sincronización de clientes sin crear accesos web automáticamente.'],
                ['name' => 'Eventos web', 'description' => 'Pedidos, presupuestos y cambios generados en la web.'],
                ['name' => 'Pedidos', 'description' => 'Actualización del estado de pedidos desde el sistema de gestión.'],
            ],
            'paths' => [
                '/api/v1/integration/health' => [
                    'get' => [
                        'tags' => ['Estado'],
                        'summary' => 'Verificar conectividad',
                        'operationId' => 'integrationHealth',
                        'responses' => [
                            '200' => $this->jsonResponse('Servicio disponible', ['type' => 'object', 'required' => ['ok', 'service', 'version', 'server_time'], 'properties' => [
                                'ok' => ['type' => 'boolean', 'example' => true],
                                'service' => ['type' => 'string', 'example' => 'fitinox-legacy-integration'],
                                'version' => ['type' => 'string', 'example' => 'v1'],
                                'server_time' => ['type' => 'string', 'format' => 'date-time'],
                            ]]),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        ],
                    ],
                ],
                '/api/v1/integration/articles' => [
                    'get' => [
                        'tags' => ['Artículos'],
                        'summary' => 'Consultar artículos recibidos',
                        'operationId' => 'listIntegratedArticles',
                        'parameters' => $this->cursorParameters(500),
                        'responses' => [
                            '200' => $this->jsonResponse('Página de artículos', ['$ref' => '#/components/schemas/ArticlePage']),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        ],
                    ],
                ],
                '/api/v1/integration/articles/upsert' => [
                    'post' => [
                        'tags' => ['Artículos'],
                        'summary' => 'Crear o actualizar artículos y stock',
                        'description' => 'Envía hasta 500 artículos. El stock sólo actualiza una variante web si coincide exactamente con cod_articulo. Reutilizar la misma Idempotency-Key al reintentar una misma solicitud.',
                        'operationId' => 'upsertArticles',
                        'parameters' => [$this->idempotencyParameter()],
                        'requestBody' => [
                            'required' => true,
                            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ArticleUpsertRequest']]],
                        ],
                        'responses' => [
                            '200' => $this->jsonResponse('Resultado de la sincronización', ['$ref' => '#/components/schemas/UpsertResult']),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                            '409' => ['$ref' => '#/components/responses/IdempotencyConflict'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/api/v1/integration/clients' => [
                    'get' => [
                        'tags' => ['Clientes'],
                        'summary' => 'Consultar clientes recibidos',
                        'operationId' => 'listIntegratedClients',
                        'parameters' => $this->cursorParameters(500),
                        'responses' => [
                            '200' => $this->jsonResponse('Página de clientes', ['$ref' => '#/components/schemas/ClientPage']),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        ],
                    ],
                ],
                '/api/v1/integration/clients/upsert' => [
                    'post' => [
                        'tags' => ['Clientes'],
                        'summary' => 'Crear o actualizar clientes',
                        'description' => 'Guarda los datos del sistema de gestión. Sólo vincula una cuenta web existente cuando coincide de forma segura por email válido o CUIT; nunca crea una cuenta de acceso.',
                        'operationId' => 'upsertClients',
                        'parameters' => [$this->idempotencyParameter()],
                        'requestBody' => [
                            'required' => true,
                            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ClientUpsertRequest']]],
                        ],
                        'responses' => [
                            '200' => $this->jsonResponse('Resultado de la sincronización', ['$ref' => '#/components/schemas/UpsertResult']),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                            '409' => ['$ref' => '#/components/responses/IdempotencyConflict'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/api/v1/integration/events' => [
                    'get' => [
                        'tags' => ['Eventos web'],
                        'summary' => 'Obtener eventos pendientes',
                        'description' => 'Devuelve eventos no confirmados de la web. Guardar cada uuid localmente para procesarlo una sola vez y confirmarlo sólo después de persistirlo en el sistema de gestión.',
                        'operationId' => 'listPendingEvents',
                        'parameters' => $this->cursorParameters(200),
                        'responses' => [
                            '200' => $this->jsonResponse('Eventos pendientes', ['$ref' => '#/components/schemas/EventPage']),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                        ],
                    ],
                ],
                '/api/v1/integration/events/acknowledge' => [
                    'post' => [
                        'tags' => ['Eventos web'],
                        'summary' => 'Confirmar eventos procesados',
                        'operationId' => 'acknowledgeEvents',
                        'requestBody' => [
                            'required' => true,
                            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/EventAcknowledgement']]],
                        ],
                        'responses' => [
                            '200' => $this->jsonResponse('Eventos confirmados', ['type' => 'object', 'required' => ['ok', 'acknowledged'], 'properties' => [
                                'ok' => ['type' => 'boolean', 'example' => true],
                                'acknowledged' => ['type' => 'integer', 'example' => 1],
                            ]]),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
                '/api/v1/integration/orders/{number}' => [
                    'patch' => [
                        'tags' => ['Pedidos'],
                        'summary' => 'Actualizar un pedido web desde el sistema de gestión',
                        'operationId' => 'updateOrderFromLegacy',
                        'parameters' => [[
                            'name' => 'number',
                            'in' => 'path',
                            'required' => true,
                            'description' => 'Número del pedido web, por ejemplo FC-20260907-00001.',
                            'schema' => ['type' => 'string'],
                        ]],
                        'requestBody' => [
                            'required' => true,
                            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/OrderUpdateRequest']]],
                        ],
                        'responses' => [
                            '200' => $this->jsonResponse('Pedido actualizado', ['$ref' => '#/components/schemas/OrderUpdateResponse']),
                            '401' => ['$ref' => '#/components/responses/Unauthorized'],
                            '404' => ['$ref' => '#/components/responses/NotFound'],
                            '422' => ['$ref' => '#/components/responses/ValidationError'],
                        ],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Opaque API token',
                        'description' => 'Token privado configurado como FITINOX_INTEGRATION_TOKEN.',
                    ],
                ],
                'responses' => [
                    'Unauthorized' => $this->jsonResponse('Token ausente o inválido', ['$ref' => '#/components/schemas/Error']),
                    'ValidationError' => $this->jsonResponse('Datos inválidos', ['$ref' => '#/components/schemas/ValidationError']),
                    'IdempotencyConflict' => $this->jsonResponse('La clave de idempotencia se reutilizó con otro cuerpo', ['$ref' => '#/components/schemas/Error']),
                    'NotFound' => $this->jsonResponse('El recurso no existe', ['$ref' => '#/components/schemas/Error']),
                ],
                'schemas' => $this->schemas(),
            ],
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function cursorParameters(int $maximum): array
    {
        return [
            ['name' => 'after_id', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'minimum' => 0, 'default' => 0]],
            ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer', 'minimum' => 1, 'maximum' => $maximum, 'default' => 100]],
        ];
    }

    private function idempotencyParameter(): array
    {
        return [
            'name' => 'Idempotency-Key',
            'in' => 'header',
            'required' => true,
            'description' => 'Identificador único de hasta 160 caracteres para reintentos seguros.',
            'schema' => ['type' => 'string', 'maxLength' => 160],
            'example' => 'articles-20260907-0001',
        ];
    }

    private function jsonResponse(string $description, array $schema): array
    {
        return ['description' => $description, 'content' => ['application/json' => ['schema' => $schema]]];
    }

    private function schemas(): array
    {
        return [
            'ArticleRecord' => [
                'type' => 'object',
                'required' => ['cod_articulo'],
                'properties' => [
                    'cod_articulo' => ['type' => 'string', 'maxLength' => 120, 'example' => 'TU-SC6LB-04-09'],
                    'configuracion' => ['type' => ['string', 'null'], 'example' => 'Tubo sin costura AISI 316'],
                    'modelo' => ['type' => ['string', 'null'], 'example' => 'TUBOS'],
                    'TipoArticulo' => ['type' => ['string', 'null'], 'example' => 'B'],
                    'fabricacion' => ['type' => ['string', 'null'], 'example' => 'I'],
                    'categoria' => ['type' => ['string', 'null'], 'example' => 'Articulo'],
                    'stock' => ['type' => ['number', 'null'], 'multipleOf' => 0.001, 'example' => 4455.2],
                    'UnMedida' => ['type' => ['string', 'null'], 'example' => 'm'],
                    'actualizado_en' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
            ],
            'ArticleUpsertRequest' => [
                'type' => 'object',
                'required' => ['records'],
                'properties' => [
                    'generated_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'records' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 500, 'items' => ['$ref' => '#/components/schemas/ArticleRecord']],
                ],
            ],
            'ClientRecord' => [
                'type' => 'object',
                'required' => ['cod_cliente', 'razon_social'],
                'properties' => [
                    'cod_cliente' => ['oneOf' => [['type' => 'string'], ['type' => 'integer']], 'example' => '2'],
                    'razon_social' => ['type' => 'string', 'maxLength' => 255, 'example' => 'ALTO PARANA S.A.'],
                    'email' => ['type' => ['string', 'null'], 'example' => 'mmorra@altoparana.com (Sr. Miguel Morra)'],
                    'Nro_documento' => ['oneOf' => [['type' => 'string'], ['type' => 'integer'], ['type' => 'null']], 'example' => '33-56324011-9'],
                    'actualizado_en' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
            ],
            'ClientUpsertRequest' => [
                'type' => 'object',
                'required' => ['records'],
                'properties' => [
                    'generated_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                    'records' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 500, 'items' => ['$ref' => '#/components/schemas/ClientRecord']],
                ],
            ],
            'UpsertResult' => [
                'type' => 'object',
                'required' => ['ok', 'resource', 'processed', 'created', 'updated', 'unchanged', 'stale', 'linked', 'unlinked'],
                'properties' => [
                    'ok' => ['type' => 'boolean', 'example' => true],
                    'resource' => ['type' => 'string', 'enum' => ['articles', 'clients']],
                    'processed' => ['type' => 'integer'],
                    'created' => ['type' => 'integer'],
                    'updated' => ['type' => 'integer'],
                    'unchanged' => ['type' => 'integer'],
                    'stale' => ['type' => 'integer'],
                    'linked' => ['type' => 'integer'],
                    'unlinked' => ['type' => 'integer'],
                ],
            ],
            'ArticlePage' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ArticleRecord']],
                    'next_after_id' => ['type' => ['integer', 'null']],
                    'has_more' => ['type' => 'boolean'],
                ],
            ],
            'ClientPage' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/ClientRecord']],
                    'next_after_id' => ['type' => ['integer', 'null']],
                    'has_more' => ['type' => 'boolean'],
                ],
            ],
            'EventAcknowledgement' => [
                'type' => 'object',
                'required' => ['event_uuids'],
                'properties' => [
                    'event_uuids' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 200, 'items' => ['type' => 'string', 'format' => 'uuid']],
                ],
            ],
            'EventPage' => [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['type' => 'object', 'properties' => [
                        'id' => ['type' => 'integer'],
                        'uuid' => ['type' => 'string', 'format' => 'uuid'],
                        'type' => ['type' => 'string', 'example' => 'order.created'],
                        'aggregate_type' => ['type' => 'string', 'example' => 'order'],
                        'aggregate_id' => ['type' => 'string', 'example' => 'FC-20260907-00001'],
                        'occurred_at' => ['type' => 'string', 'format' => 'date-time'],
                        'payload' => ['type' => 'object', 'additionalProperties' => true],
                    ]]],
                    'next_after_id' => ['type' => ['integer', 'null']],
                    'has_more' => ['type' => 'boolean'],
                ],
            ],
            'OrderUpdateRequest' => [
                'type' => 'object',
                'required' => ['status'],
                'properties' => [
                    'external_reference' => ['type' => ['string', 'null'], 'maxLength' => 255, 'example' => 'PV-15468'],
                    'status' => ['type' => 'string', 'enum' => ['received', 'processing', 'quoted', 'completed', 'cancelled']],
                    'delivery_date' => ['type' => ['string', 'null'], 'format' => 'date', 'example' => '2026-09-12'],
                ],
            ],
            'OrderUpdateResponse' => [
                'type' => 'object',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'numero_web' => ['type' => 'string'],
                    'referencia_sistema' => ['type' => ['string', 'null']],
                    'estado' => ['type' => 'string'],
                    'fecha_entrega' => ['type' => ['string', 'null'], 'format' => 'date'],
                    'sincronizado_en' => ['type' => ['string', 'null'], 'format' => 'date-time'],
                ],
            ],
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'code' => ['type' => 'string'],
                ],
            ],
            'ValidationError' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/Error'],
                    ['type' => 'object', 'properties' => ['errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]]]],
                ],
            ],
        ];
    }
}
