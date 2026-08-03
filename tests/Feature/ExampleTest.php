<?php

namespace Tests\Feature;

use App\Models\Download;
use Database\Seeders\FitinoxSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_uses_the_public_responsive_navigation_contract(): void
    {
        $this->seed(FitinoxSeeder::class);
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('data-mobile-nav-root', false)
            ->assertSee('data-mobile-nav-trigger', false)
            ->assertSee('data-mobile-nav', false)
            ->assertSee('data-mobile-nav-overlay', false)
            ->assertSee('data-mobile-nav-close', false);

        $this->assertSame(1, substr_count($response->getContent(), '<html'));
    }

    public function test_downloads_remain_in_public_navigation_when_no_document_is_visible(): void
    {
        $this->seed(FitinoxSeeder::class);
        Download::query()->update(['is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="/descargas"', false);

        $this->get('/descargas')
            ->assertOk()
            ->assertSee('Aún no existe contenido disponible');
    }
}
