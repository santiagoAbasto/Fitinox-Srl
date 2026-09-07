<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\ProductVariant;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TurnstileService;
use App\Services\IntegrationOutbox;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('variant')) {
            $variant = ProductVariant::with(['product.category'])->where('is_active', true)->findOrFail($request->integer('variant'));
            abort_unless($variant->product?->is_active && $variant->product?->category?->is_active, 404);
            $cart = session('quote_cart', []);
            $key = (string) $variant->id;
            $cart[$key] = [
                'variant_id' => $variant->id,
                'product_id' => $variant->product->id,
                'product_name' => $variant->product->name,
                'category_name' => $variant->product->category->name,
                'image' => $variant->product->main_image,
                'code' => $variant->code,
                'tube' => $variant->tube,
                'thread' => $variant->thread,
                'quantity' => max(1, min(999, $request->integer('quantity', 1))),
            ];
            session(['quote_cart' => $cart]);
            return redirect()->route('quote.index');
        }

        return view('quote.index', [
            'page' => Page::where('slug', 'presupuesto')->where('is_published', true)->firstOrFail(),
            'items' => collect(session('quote_cart', []))->values(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function updateItem(Request $request, int $variant)
    {
        $quantity = $request->validate(['quantity' => ['required', 'integer', 'between:1,999']])['quantity'];
        $cart = session('quote_cart', []);
        if (isset($cart[$variant])) {
            $cart[$variant]['quantity'] = $quantity;
            session(['quote_cart' => $cart]);
        }
        return back();
    }

    public function removeItem(int $variant)
    {
        $cart = session('quote_cart', []);
        unset($cart[$variant]);
        session(['quote_cart' => $cart]);
        return back()->with('success', 'Producto quitado de la solicitud.');
    }

    public function store(Request $request, TurnstileService $turnstile, IntegrationOutbox $outbox)
    {
        $turnstile->validate($request, 'quote');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $items = collect(session('quote_cart', []))->values();
        if ($items->isEmpty()) return back()->withErrors(['cart' => 'Agregá al menos un producto antes de enviar la solicitud.']);

        $quote = DB::transaction(function () use ($data, $items) {
            $quote = QuoteRequest::create($data);
            foreach ($items as $item) {
                $quote->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['product_name'],
                    'category_name' => $item['category_name'],
                    'code' => $item['code'],
                    'tube' => $item['tube'],
                    'thread' => $item['thread'],
                    'quantity' => $item['quantity'],
                ]);
            }
            return $quote;
        });
        $outbox->quote($quote);
        session()->forget('quote_cart');

        return redirect()->route('quote.index')->with('success', 'Tu solicitud fue enviada correctamente. El equipo de Fitinox se pondrá en contacto.');
    }
}
