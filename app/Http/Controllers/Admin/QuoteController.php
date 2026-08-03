<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function index()
    {
        return Inertia::render('Quotes/Index', [
            'page' => Page::where('slug', 'presupuesto')->firstOrFail(),
            'requests' => QuoteRequest::with('items')
                ->latest()
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'presupuesto')->firstOrFail();
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('quotes/hero', 'public') ?? $page->hero_image,
        ]);

        return back()->with('success', 'Portada de Presupuesto guardada correctamente.');
    }

    public function updateStatus(Request $request, QuoteRequest $quoteRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,in_progress,quoted,closed'],
        ]);
        $quoteRequest->update($data);

        return back()->with('success', 'Estado de la solicitud actualizado.');
    }
}
