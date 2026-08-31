<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_category_without_its_own_image_inherits_the_nearest_parent_image(): void
    {
        $main = Category::create([
            'name' => 'Accesorios para tubos',
            'slug' => 'accesorios-para-tubos',
            'image' => 'categories/images/main.jpg',
            'is_active' => true,
            'sort_order' => 'A',
        ]);
        $child = Category::create([
            'parent_id' => $main->id,
            'name' => 'Conectores AJA-LOCK',
            'slug' => 'conectores-aja-lock',
            'is_active' => true,
            'sort_order' => 'A',
        ]);
        $grandchild = Category::create([
            'parent_id' => $child->id,
            'name' => 'Pulgada',
            'slug' => 'pulgada',
            'is_active' => true,
            'sort_order' => 'A',
        ]);

        $this->assertSame('categories/images/main.jpg', $grandchild->load('parent.parent')->resolvedImage());

        $child->update(['image' => 'categories/images/child.jpg']);

        $this->assertSame('categories/images/child.jpg', $grandchild->fresh()->load('parent.parent')->resolvedImage());
    }

    public function test_admin_can_assign_an_optional_image_to_a_subcategory(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $main = Category::create([
            'name' => 'Accesorios para tubos',
            'slug' => 'accesorios-para-tubos',
            'image' => 'categories/images/main.jpg',
            'is_active' => true,
            'sort_order' => 'A',
        ]);

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'parent_id' => $main->id,
            'name' => 'Conectores AJA-LOCK',
            'description' => 'Conectores de instrumentación.',
            'image_file' => UploadedFile::fake()->image('conectores.jpg', 1200, 1200),
            'is_featured' => true,
            'is_active' => true,
            'sort_order' => 'A',
        ]);

        $response->assertSessionHasNoErrors();
        $child = Category::where('name', 'Conectores AJA-LOCK')->firstOrFail();

        $this->assertSame($main->id, $child->parent_id);
        $this->assertFalse($child->is_featured);
        $this->assertNotNull($child->image);
        Storage::disk('public')->assertExists($child->image);
    }

    public function test_public_category_page_lists_child_cards_before_products(): void
    {
        $main = Category::create([
            'name' => 'Accesorios para tubos',
            'slug' => 'accesorios-para-tubos',
            'image' => 'categories/images/main.jpg',
            'is_active' => true,
            'sort_order' => 'A',
        ]);
        $child = Category::create([
            'parent_id' => $main->id,
            'name' => 'Conectores AJA-LOCK',
            'slug' => 'conectores-aja-lock',
            'is_active' => true,
            'sort_order' => 'A',
        ]);
        Product::create([
            'category_id' => $child->id,
            'name' => 'Recto Macho',
            'slug' => 'recto-macho',
            'is_active' => true,
            'sort_order' => 'A',
        ]);

        $response = $this->get(route('products.category', $main));

        $response->assertOk();
        $response->assertSee(route('products.category', $child), false);
        $response->assertSee('/storage/categories/images/main.jpg', false);
        $response->assertSee('data-public-hero', false);
        $response->assertDontSee('Recto Macho');

        $childResponse = $this->get(route('products.category', $child));
        $childResponse->assertOk();
        $childResponse->assertSee('aria-label="Categorías de productos"', false);
        $childResponse->assertDontSee('id="products-heading"', false);
        $childResponse->assertSee('aria-label="Productos de Conectores AJA-LOCK"', false);
        $childResponse->assertSee('Recto Macho');
    }
}
