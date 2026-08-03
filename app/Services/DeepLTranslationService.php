<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DeepLTranslationService
{
    public function translateMany(array $texts, string $target = 'EN-US', string $source = 'ES'): array
    {
        $texts = array_values(array_unique(array_filter(array_map('trim', $texts))));
        if ($texts === []) return [];

        // DeepL puede conservar palabras cortas en mayúscula como si fueran
        // siglas (por ejemplo "CLIENTES"). Esta capa fija la interfaz del CMS
        // y de Zona Clientes antes de consultar/cargar cualquier traducción.
        $fixed = [];
        foreach ($texts as $index => $text) {
            $translation = $this->interfaceTranslation($text, $target);
            if ($translation === null) continue;
            $fixed[$text] = $translation;
            unset($texts[$index]);
        }
        $texts = array_values($texts);
        if ($texts === []) return $fixed;

        // La web nunca debe romperse si una instalación todavía no corrió la
        // migración o si la clave se deshabilita momentáneamente.
        if (!config('services.deepl.key') || !Schema::hasTable('translations')) {
            return $fixed + (array_combine($texts, $texts) ?: []);
        }

        $hashes = collect($texts)->mapWithKeys(fn (string $text) => [
            $this->hash($text, $source, $target) => $text,
        ]);
        $cached = Translation::whereIn('hash', $hashes->keys())
            ->pluck('translated_text', 'hash');
        $result = $fixed;
        $missing = [];

        foreach ($hashes as $hash => $text) {
            if ($cached->has($hash)) $result[$text] = $cached[$hash];
            else $missing[$hash] = $text;
        }

        foreach ($this->chunks($missing) as $chunk) {
            try {
                $response = Http::timeout(25)
                    ->retry(2, 400)
                    ->withHeaders([
                        'Authorization' => 'DeepL-Auth-Key '.config('services.deepl.key'),
                        'Content-Type' => 'application/json',
                    ])
                    ->post(rtrim(config('services.deepl.url'), '/').'/v2/translate', [
                        'text' => array_values($chunk),
                        'source_lang' => $source,
                        'target_lang' => $target,
                        'preserve_formatting' => true,
                    ])
                    ->throw()
                    ->json('translations');

                foreach (array_values($chunk) as $index => $sourceText) {
                    $translated = $response[$index]['text'] ?? $sourceText;
                    $hash = $this->hash($sourceText, $source, $target);
                    $result[$sourceText] = $translated;
                    Translation::updateOrCreate(['hash' => $hash], [
                        'source_locale' => $source,
                        'target_locale' => $target,
                        'source_text' => $sourceText,
                        'translated_text' => $translated,
                    ]);
                }
            } catch (\Throwable $exception) {
                Log::warning('DeepL translation failed', ['message' => $exception->getMessage()]);
                foreach ($chunk as $sourceText) $result[$sourceText] = $sourceText;
            }
        }

        return $result;
    }

    private function interfaceTranslation(string $text, string $target): ?string
    {
        if (!str_starts_with(strtoupper($target), 'EN')) return null;

        return [
            'CLIENTES' => 'CLIENTS',
            'Clientes' => 'Clients',
            'Zona Clientes' => 'Client Area',
            'ZONA CLIENTES' => 'CLIENT AREA',
            'Productos' => 'Products',
            'Carrito' => 'Cart',
            'Lista de precios' => 'Price list',
            'Histórico de pedidos' => 'Order history',
            'Buscar productos' => 'Search products',
            'Cerrar sesión' => 'Sign out',
            'Contacto' => 'Contact',
            'Suscribite al Newsletter' => 'Subscribe to the newsletter',
            'Seguinos en' => 'Follow us',
            'Inicio' => 'Home',
            'Migas de pan' => 'Breadcrumb',
            'VER ONLINE' => 'VIEW ONLINE',
            'DESCARGAR' => 'DOWNLOAD',
            'DESCARGAR PDF' => 'DOWNLOAD PDF',
            'VER PRODUCTOS' => 'VIEW PRODUCTS',
            'VER LISTA DE PRECIOS' => 'VIEW PRICE LIST',
            'N° de pedido' => 'Order number',
            'Fecha de entrega' => 'Delivery date',
            'Importe' => 'Amount',
            'Producto' => 'Product',
            'Código' => 'Code',
            'Cantidad' => 'Quantity',
            'Precio' => 'Price',
            'Descuento' => 'Discount',
            'Precio con descuento' => 'Discounted price',
            'Stock físico' => 'Physical stock',
            'Stock en tránsito' => 'In-transit stock',
            'Subtotal' => 'Subtotal',
            'Total' => 'Total',
            'Entrega' => 'Delivery',
        ][$text] ?? null;
    }

    /**
     * DeepL permite varios textos por solicitud, pero agrupar por cantidad de
     * caracteres evita superar el tamaño de cuerpo cuando un editor agrega un
     * bloque institucional extenso.
     */
    private function chunks(array $texts): array
    {
        $chunks = [];
        $chunk = [];
        $length = 0;

        foreach ($texts as $hash => $text) {
            $textLength = mb_strlen($text);
            if ($chunk !== [] && (count($chunk) >= 40 || $length + $textLength > 24000)) {
                $chunks[] = $chunk;
                $chunk = [];
                $length = 0;
            }

            $chunk[$hash] = $text;
            $length += $textLength;
        }

        if ($chunk !== []) $chunks[] = $chunk;

        return $chunks;
    }

    private function hash(string $text, string $source, string $target): string
    {
        return hash('sha256', $source.'|'.$target.'|'.$text);
    }
}
