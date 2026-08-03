<?php

namespace Tests\Feature;

use Database\Seeders\FitinoxSeeder;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicResponsiveMarkupTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_exposes_the_public_navigation_contract(): void
    {
        $this->seed(FitinoxSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $xpath = $this->xpath($response->getContent());

        $this->assertSame(1, $xpath->query('//aside[@id="mobile-public-navigation" and @data-mobile-nav and @aria-hidden="true" and @aria-label]')->length);
        $this->assertSame(1, $xpath->query('//button[@data-mobile-nav-trigger and @aria-controls="mobile-public-navigation" and @aria-expanded="false" and @aria-label]')->length);
        $this->assertSame(1, $xpath->query('//button[@data-mobile-nav-overlay and @aria-label and @tabindex="-1"]')->length);
        $this->assertSame(1, $xpath->query('//button[@data-mobile-nav-close and @aria-label]')->length);

        foreach (['/nosotros' => 'Nosotros', '/productos' => 'Productos', '/contacto' => 'Contacto'] as $href => $label) {
            $links = $xpath->query('//aside[@data-mobile-nav]//a[@href="'.$href.'" and normalize-space()="'.$label.'"]');
            $this->assertSame(1, $links->length, $label);
        }

        $this->assertSame(1, $xpath->query('//footer[@id="footer"]')->length);
    }

    public function test_home_exposes_search_metadata(): void
    {
        $this->seed(FitinoxSeeder::class);
        $response = $this->get('/');

        $response->assertOk();
        $xpath = $this->xpath($response->getContent());

        $this->assertSame(1, $xpath->query('//title[string-length(normalize-space()) > 0]')->length);
        $this->assertSame(1, $xpath->query('//meta[@name="description" and string-length(normalize-space(@content)) > 0]')->length);
    }

    public function test_all_public_sections_use_the_shared_responsive_layout(): void
    {
        $this->seed(FitinoxSeeder::class);

        foreach (['/', '/nosotros', '/productos', '/descargas', '/calidad', '/videos', '/distribuidores', '/presupuesto', '/contacto'] as $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $response->assertSee('data-mobile-nav-trigger', false);
            $response->assertSee('data-mobile-nav', false);
            $this->assertSame(1, substr_count($response->getContent(), '<html'), $uri);
        }
    }

    public function test_client_area_exposes_its_mobile_navigation_on_every_main_screen(): void
    {
        $this->seed(FitinoxSeeder::class);
        $client = User::factory()->create([
            'role' => 'client',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        foreach (['/clientes/productos', '/clientes/carrito', '/clientes/lista-de-precios', '/clientes/pedidos'] as $uri) {
            $response = $this->actingAs($client)->get($uri);
            $response->assertOk();
            $response->assertSee('id="mobile-client-navigation"', false);
            $response->assertSee('data-mobile-nav-trigger', false);
            $response->assertSee('data-footer-copyright', false);
            $response->assertSee('data-floating-whatsapp', false);
        }
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
