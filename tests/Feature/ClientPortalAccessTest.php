<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientPortalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_without_price_list_permission_cannot_open_private_price_lists(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'can_view_price_lists' => false,
        ]);

        $this->actingAs($client, 'client')
            ->get('/clientes/lista-de-precios')
            ->assertForbidden();
    }

    public function test_cart_displays_the_real_discount_and_requires_a_transport_name_only_for_transport_delivery(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'discount_percent' => 2,
        ]);
        $variant = $this->variant();

        $this->actingAs($client, 'client')
            ->withSession(['fitinox_client_cart' => [$variant->id => 1]])
            ->get('/clientes/carrito')
            ->assertOk()
            ->assertSee('-2%')
            ->assertDontSee('Otros impuestos');

        $this->actingAs($client, 'client')
            ->withSession(['fitinox_client_cart' => [$variant->id => 1]])
            ->post('/clientes/pedidos', ['delivery_method' => 'transport'])
            ->assertSessionHasErrors('transport_name')
            ->assertSessionDoesntHaveErrors(['transport_address', 'transport_phone', 'delivery_destination']);
    }

    public function test_administrator_and_client_guards_can_coexist_in_the_same_browser_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $client = User::factory()->create(['role' => 'client', 'is_active' => true]);

        $this->actingAs($admin)
            ->actingAs($client, 'client');
        // actingAs() cambia el guard por defecto sólo en el test; el navegador
        // real conserva `web` como default y el middleware de clientes pide el
        // guard explícito `client`.
        Auth::shouldUse('web');

        $this->get('/admin/zona-clientes')->assertOk();

        $this->get('/clientes/productos')->assertOk();
    }

    public function test_logging_into_the_client_zone_does_not_log_out_an_administrator(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('admin-password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $client = User::factory()->create([
            'email' => 'cliente@example.com',
            'password' => Hash::make('client-password'),
            'role' => 'client',
            'is_active' => true,
        ]);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'admin-password'])
            ->assertRedirect('/admin');

        $this->post('/admin/login', ['login' => $client->email, 'password' => 'client-password', 'access' => 'client'])
            ->assertRedirect('/clientes');

        $this->get('/admin/analytics')->assertOk();
        $this->get('/clientes/productos')->assertOk();
    }

    private function variant(): ProductVariant
    {
        $category = Category::create(['name' => 'Clientes', 'slug' => 'clientes', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Filtro de prueba', 'slug' => 'filtro-prueba', 'is_active' => true]);

        return ProductVariant::create(['product_id' => $product->id, 'code' => 'CLI-001', 'list_price' => 100, 'is_active' => true]);
    }
}
