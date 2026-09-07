<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClientOrder;
use App\Models\IntegrationArticle;
use App\Models\IntegrationOutboxEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\IntegrationOutbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LegacyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('integration.token', 'integration-test-token');
    }

    public function test_integration_requires_a_valid_bearer_token(): void
    {
        $this->getJson('/api/v1/integration/health')->assertUnauthorized();

        $this->withToken('wrong-token')->getJson('/api/v1/integration/health')->assertUnauthorized();

        $this->withToken('integration-test-token')
            ->getJson('/api/v1/integration/health')
            ->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_openapi_spec_and_documentation_are_publicly_available_without_the_integration_token(): void
    {
        $this->getJson('/api/openapi.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.1.0')
            ->assertJsonPath('components.securitySchemes.bearerAuth.scheme', 'bearer')
            ->assertJsonPath('paths./api/v1/integration/articles/upsert.post.operationId', 'upsertArticles');

        $this->get('/integracion/api')
            ->assertOk()
            ->assertSee('swagger-ui-bundle.js')
            ->assertSee('openapi.json');
    }

    public function test_token_command_generates_a_96_character_secret_without_writing_configuration_by_default(): void
    {
        $this->assertSame(0, Artisan::call('integration:token'));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{96}/', Artisan::output());
    }

    public function test_it_upserts_articles_updates_decimal_stock_and_replays_identical_requests(): void
    {
        $variant = $this->variant('TU-SC6LB-04-09');
        $payload = [
            'generated_at' => '2026-09-07T12:00:00-03:00',
            'records' => [[
                'cod_articulo' => 'TU-SC6LB-04-09',
                'configuracion' => 'Tubo sin costura AISI 316',
                'modelo' => 'TUBOS',
                'TipoArticulo' => 'B',
                'fabricacion' => 'I',
                'categoria' => 'Articulo',
                'stock' => 4455.2,
                'UnMedida' => 'm',
            ]],
        ];

        $response = $this->withToken('integration-test-token')
            ->withHeader('Idempotency-Key', 'articles-001')
            ->postJson('/api/v1/integration/articles/upsert', $payload)
            ->assertOk()
            ->assertJsonPath('processed', 1)
            ->assertJsonPath('linked', 1);

        $this->assertSame(4455.2, (float) $variant->fresh()->physical_stock);
        $this->assertDatabaseHas('integration_articles', [
            'external_code' => 'TU-SC6LB-04-09',
            'product_variant_id' => $variant->id,
        ]);

        $this->withToken('integration-test-token')
            ->withHeader('Idempotency-Key', 'articles-001')
            ->postJson('/api/v1/integration/articles/upsert', $payload)
            ->assertOk()
            ->assertHeader('Idempotency-Replayed', 'true');

        $this->assertSame(1, IntegrationArticle::count());

        $this->withToken('integration-test-token')
            ->withHeader('Idempotency-Key', 'articles-001')
            ->postJson('/api/v1/integration/articles/upsert', [
                'records' => [[
                    'cod_articulo' => 'TU-SC6LB-04-09',
                    'stock' => 100,
                ]],
            ])
            ->assertConflict()
            ->assertJsonPath('code', 'idempotency_conflict');

        $this->assertSame(4455.2, (float) $variant->fresh()->physical_stock);
    }

    public function test_it_preserves_legacy_client_data_without_creating_portal_users(): void
    {
        $this->withToken('integration-test-token')
            ->withHeader('Idempotency-Key', 'clients-001')
            ->postJson('/api/v1/integration/clients/upsert', [
                'records' => [[
                    'cod_cliente' => 2,
                    'razon_social' => 'ALTO PARANA S.A.',
                    'email' => 'mmorra@altoparana.com (Sr. Miguel Morra)',
                    'Nro_documento' => '33-56324011-9',
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('created', 1)
            ->assertJsonPath('unlinked', 1);

        $this->assertDatabaseHas('integration_clients', [
            'external_code' => '2',
            'business_name' => 'ALTO PARANA S.A.',
            'user_id' => null,
        ]);
        $this->assertSame(0, User::where('role', 'client')->count());
    }

    public function test_it_links_an_existing_portal_user_using_a_valid_email_or_tax_id(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
            'company' => 'Nombre anterior',
            'tax_id' => '30-50065891-2',
        ]);

        $this->withToken('integration-test-token')
            ->withHeader('Idempotency-Key', 'clients-002')
            ->postJson('/api/v1/integration/clients/upsert', [
                'records' => [[
                    'cod_cliente' => 6,
                    'razon_social' => 'ATANOR S.A.',
                    'email' => 'nsessarego@atanor.com.ar',
                    'Nro_documento' => '30-50065891-2',
                ]],
            ])
            ->assertOk()
            ->assertJsonPath('linked', 1);

        $this->assertDatabaseHas('integration_clients', [
            'external_code' => '6',
            'user_id' => $user->id,
        ]);
        $this->assertSame('ATANOR S.A.', $user->fresh()->company);
    }

    public function test_it_exposes_events_once_and_acknowledges_them(): void
    {
        $order = ClientOrder::create([
            'user_id' => User::factory()->create(['role' => 'client'])->id,
            'number' => 'FC-20260907-00001',
            'status' => 'received',
            'subtotal' => 10,
            'discount_total' => 0,
            'total' => 10,
        ]);
        app(IntegrationOutbox::class)->order($order, 'order.created');
        $event = IntegrationOutboxEvent::firstOrFail();

        $this->withToken('integration-test-token')
            ->getJson('/api/v1/integration/events')
            ->assertOk()
            ->assertJsonPath('data.0.uuid', $event->uuid)
            ->assertJsonPath('data.0.type', 'order.created');

        $this->withToken('integration-test-token')
            ->postJson('/api/v1/integration/events/acknowledge', ['event_uuids' => [$event->uuid]])
            ->assertOk()
            ->assertJsonPath('acknowledged', 1);

        $this->assertNotNull($event->fresh()->acknowledged_at);
    }

    private function variant(string $code): ProductVariant
    {
        $category = Category::create(['name' => 'Pruebas API', 'slug' => 'pruebas-api', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Tubo de prueba',
            'slug' => 'tubo-de-prueba',
            'is_active' => true,
        ]);

        return ProductVariant::create(['product_id' => $product->id, 'code' => $code, 'is_active' => true]);
    }
}
