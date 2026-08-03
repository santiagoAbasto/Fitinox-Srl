<?php

namespace App\Http\Controllers;

use App\Models\ClientOrder;
use App\Models\ClientPriceList;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Services\DeepLTranslationService;
use App\Mail\ClientOrderMail;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;

class ClientPortalController extends Controller
{
    private function client(Request $request)
    {
        // Estas rutas ya están protegidas por `auth`. El acceso activo se valida
        // durante el inicio de sesión; no duplicamos la condición aquí para que
        // una sesión recién regenerada no termine en un 403 espurio.
        return $request->user();
    }

    public function dashboard(Request $request)
    {
        return redirect()->route('client.products');
    }

    public function products(Request $request)
    {
        $client = $this->client($request);
        $variants = ProductVariant::query()
            ->where('is_active', true)
            ->whereHas('product', fn ($product) => $product->where('is_active', true)->whereHas('category', fn ($category) => $category->where('is_active', true)))
            ->with(['product.category'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->string('q'));
                $query->where(function ($q) use ($term) {
                    $q->where('code', 'like', "%{$term}%")
                        ->orWhereHas('product', fn ($product) => $product->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->whereHas('product', fn ($product) => $product->where('category_id', $request->integer('category'))))
            ->orderBy('code')
            ->paginate(50)
            ->withQueryString();

        return view('client.products', [
            'client' => $client,
            'variants' => $variants,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'query' => $request->only(['q', 'category']),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function priceList(Request $request)
    {
        $client = $this->client($request);
        return view('client.price-list', [
            'client' => $client,
            'priceLists' => ClientPriceList::query()->where('is_active', true)->orderBy('sort_order')->orderByDesc('id')->get(),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function cart(Request $request)
    {
        $client = $this->client($request);
        $items = $this->cartItems($request);
        return view('client.cart', ['client' => $client, 'items' => $items, 'cartCount' => $this->cartCount($request)]);
    }

    public function addToCart(Request $request, ProductVariant $variant)
    {
        $this->client($request);
        abort_unless($variant->is_active && $variant->product?->is_active && $variant->product?->category?->is_active, 404);
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:0', 'max:999']]);
        $cart = $request->session()->get('fitinox_client_cart', []);
        $cart[$variant->id] = min(999, ($cart[$variant->id] ?? 0) + max(1, $data['quantity'] ?? 1));
        $request->session()->put('fitinox_client_cart', $cart);
        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function updateCart(Request $request, ProductVariant $variant)
    {
        $this->client($request);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:999']]);
        $cart = $request->session()->get('fitinox_client_cart', []);
        if ($data['quantity'] === 0) unset($cart[$variant->id]); else $cart[$variant->id] = $data['quantity'];
        $request->session()->put('fitinox_client_cart', $cart);
        return back()->with('success', 'Carrito actualizado.');
    }

    public function checkout(Request $request)
    {
        $client = $this->client($request);
        $items = $this->cartItems($request);
        abort_if($items->isEmpty(), 422, 'El carrito está vacío.');
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'delivery_method' => ['required', 'in:pickup,transport'],
            'transport_name' => ['nullable', 'string', 'max:120', 'required_if:delivery_method,transport'],
            'transport_address' => ['nullable', 'string', 'max:255', 'required_if:delivery_method,transport'],
            'transport_phone' => ['nullable', 'string', 'max:60', 'required_if:delivery_method,transport'],
            'delivery_destination' => ['nullable', 'string', 'max:255', 'required_if:delivery_method,transport'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:10240'],
        ]);
        $attachmentPath = $request->file('attachment')?->store('client-orders', 'public');

        $order = DB::transaction(function () use ($client, $items, $data, $attachmentPath) {
            $subtotal = $items->sum('list_total');
            $total = $items->sum('total');
            $order = ClientOrder::create([
                'user_id' => $client->id,
                'number' => 'FC-'.now()->format('Ymd').'-'.str_pad((string) (ClientOrder::max('id') + 1), 5, '0', STR_PAD_LEFT),
                'status' => 'received',
                'subtotal' => $subtotal,
                'discount_total' => $subtotal - $total,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'delivery_method' => $data['delivery_method'],
                'transport_name' => $data['transport_name'] ?? null,
                'transport_address' => $data['transport_address'] ?? null,
                'transport_phone' => $data['transport_phone'] ?? null,
                'delivery_destination' => $data['delivery_destination'] ?? null,
                'attachment_path' => $attachmentPath,
            ]);
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['variant']->product_id,
                    'product_variant_id' => $item['variant']->id,
                    'product_name' => $item['variant']->product->name,
                    'code' => $item['variant']->code,
                    'quantity' => $item['quantity'],
                    'list_price' => $item['list_price'],
                    'discount_percent' => $item['discount'],
                    'unit_price' => $item['price'],
                    'line_total' => $item['total'],
                ]);
            }
            return $order;
        });
        $request->session()->forget('fitinox_client_cart');
        try {
            $order->load('user', 'items');
            Mail::to($client->email)->send(new ClientOrderMail($order, 'client'));
            $recipient = Setting::where('key', 'email_sales')->value('value');
            if ($recipient) Mail::to($recipient)->send(new ClientOrderMail($order, 'team'));
        } catch (\Throwable $exception) { report($exception); }
        return redirect()->route('client.orders')->with('success', 'Pedido '.$order->number.' enviado correctamente.');
    }

    public function orders(Request $request)
    {
        $client = $this->client($request);
        return view('client.orders', ['client' => $client, 'orders' => $client->clientOrders()->with('items')->latest()->paginate(15), 'cartCount' => $this->cartCount($request)]);
    }

    public function showOrder(Request $request, ClientOrder $clientOrder)
    {
        $client = $this->client($request);
        abort_unless($clientOrder->user_id === $client->id, 404);

        return view('client.order-document', [
            'client' => $client,
            'order' => $clientOrder->load('items'),
            'cartCount' => $this->cartCount($request),
        ]);
    }

    public function downloadOrder(Request $request, ClientOrder $clientOrder)
    {
        $client = $this->client($request);
        abort_unless($clientOrder->user_id === $client->id, 404);
        $clientOrder->load('items');
        $locale = app()->getLocale();
        $translations = $this->pdfTranslations([
            ...$clientOrder->items->pluck('product_name')->all(),
            $clientOrder->notes,
        ], $locale);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('client.order-pdf', [
            'client' => $client,
            'order' => $clientOrder,
            'logo' => $this->publicDataUri('figma/header-footer/header-logo.png'),
            'locale' => $locale,
            'translations' => $translations,
        ])->render());
        $pdf->setPaper('a4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="pedido-'.strtolower($clientOrder->number).'.pdf"',
        ]);
    }

    public function viewPriceList(Request $request, ClientPriceList $clientPriceList)
    {
        $this->client($request);
        abort_unless($clientPriceList->is_active && Storage::disk('public')->exists($clientPriceList->file_path), 404);

        $extension = strtolower(pathinfo($clientPriceList->file_path, PATHINFO_EXTENSION));
        $office = in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf'], true);
        $host = $request->getHost();

        return view('client.price-list-viewer', [
            'client' => $request->user(),
            'priceList' => $clientPriceList,
            'extension' => $extension,
            'office' => $office,
            'isLocal' => in_array($host, ['127.0.0.1', 'localhost', '::1'], true),
            'rawUrl' => url(Storage::disk('public')->url($clientPriceList->file_path)),
            'downloadUrl' => route('client.price-list.download', $clientPriceList),
        ]);
    }

    public function downloadPriceList(Request $request, ClientPriceList $clientPriceList)
    {
        $this->client($request);
        abort_unless($clientPriceList->is_active && Storage::disk('public')->exists($clientPriceList->file_path), 404);
        return Storage::disk('public')->download($clientPriceList->file_path, $clientPriceList->name.'.'.pathinfo($clientPriceList->file_path, PATHINFO_EXTENSION));
    }

    private function cartItems(Request $request)
    {
        $cart = $request->session()->get('fitinox_client_cart', []);
        return ProductVariant::with('product.category')->whereIn('id', array_keys($cart))->where('is_active', true)->get()->map(function ($variant) use ($cart) {
            $listPrice = (float) ($variant->list_price ?? $variant->final_price ?? 0);
            $price = (float) ($variant->final_price ?? $listPrice);
            $quantity = (int) $cart[$variant->id];
            return ['variant' => $variant, 'quantity' => $quantity, 'list_price' => $listPrice, 'price' => $price, 'discount' => (float) $variant->discount_percent, 'list_total' => $listPrice * $quantity, 'total' => $price * $quantity];
        });
    }

    private function cartCount(Request $request): int { return array_sum($request->session()->get('fitinox_client_cart', [])); }

    /**
     * Los PDF se renderizan directamente con Dompdf y no atraviesan el
     * middleware HTML. Por eso traducimos los campos administrables antes de
     * construir la vista, usando la misma caché/API DeepL de la web pública.
     */
    private function pdfTranslations(array $texts, string $locale): array
    {
        if ($locale !== 'en') return [];

        return app(DeepLTranslationService::class)->translateMany(array_filter(
            $texts,
            fn ($text) => is_string($text) && trim($text) !== ''
        ));
    }

    private function publicDataUri(string $path): ?string
    {
        $absolute = public_path('storage/'.$path);
        if (! is_file($absolute)) return null;

        return 'data:'.mime_content_type($absolute).';base64,'.base64_encode(file_get_contents($absolute));
    }
}
