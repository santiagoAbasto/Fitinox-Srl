<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\FitinoxSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_html_responses_include_security_headers_and_report_only_csp(): void
    {
        $this->seed(FitinoxSeeder::class);
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Content-Security-Policy-Report-Only');
    }

    public function test_inactive_administrator_cannot_access_the_panel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'admin', 'is_active' => false])->save();
        $this->actingAs($user)->get('/admin/analytics')->assertForbidden();
    }

    public function test_editor_cannot_mutate_commercial_products(): void
    {
        $editor = User::factory()->create();
        $editor->forceFill(['role' => 'editor', 'is_active' => true])->save();
        $category = Category::create(['name' => 'Segura', 'slug' => 'segura', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Producto', 'slug' => 'producto', 'sort_order' => 'A', 'is_active' => true]);

        $this->actingAs($editor)->delete(route('admin.products.destroy', $product))->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_sql_injection_payload_is_treated_as_search_text(): void
    {
        $this->seed(FitinoxSeeder::class);
        $this->getJson('/buscar/productos?q=%27%20OR%201%3D1--')->assertOk();
    }
}
