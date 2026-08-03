<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\QualityDocument;
use App\Support\OrderCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class QualityController extends Controller
{
    public function edit()
    {
        return Inertia::render('Quality/Edit', [
            'page' => Page::where('slug', 'calidad')->firstOrFail(),
            'documents' => QualityDocument::orderBy('sort_order')->orderBy('id')->get(),
            'nextOrder' => OrderCode::next(QualityDocument::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:150'],
            'content_title' => ['required', 'max:150'],
            'content' => ['required'],
            'downloads_title' => ['required', 'max:150'],
            'policies' => ['required', 'array', 'min:1'],
            'policies.*' => ['required', 'string', 'max:300'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'content_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'calidad')->firstOrFail();
        $sections = $page->sections ?? [];
        $sections['content_title'] = $data['content_title'];
        $sections['downloads_title'] = $data['downloads_title'];
        $sections['policies'] = array_values($data['policies']);
        if ($request->hasFile('content_image_file')) {
            $sections['content_image'] = $request->file('content_image_file')->store('quality/images', 'public');
        }
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('quality/images', 'public') ?? $page->hero_image,
            'content' => $data['content'],
            'sections' => $sections,
        ]);

        return back()->with('success', 'Contenido de Calidad guardado correctamente.');
    }

    public function store(Request $request)
    {
        QualityDocument::create($this->validatedDocument($request));

        return back()->with('success', 'Documento de calidad agregado.');
    }

    public function update(Request $request, QualityDocument $document)
    {
        $document->update($this->validatedDocument($request, $document));

        return back()->with('success', 'Documento de calidad actualizado.');
    }

    public function destroy(QualityDocument $document)
    {
        if ($document->file) Storage::disk('public')->delete($document->file);
        if ($document->logo && !str_starts_with($document->logo, 'figma/')) Storage::disk('public')->delete($document->logo);
        $document->delete();

        return back()->with('success', 'Documento de calidad eliminado.');
    }

    private function validatedDocument(Request $request, ?QualityDocument $document = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'max:180'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
            'is_active' => ['required', 'boolean'],
            'file_upload' => [$document ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:51200'],
            'logo_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        unset($data['file_upload'], $data['logo_upload']);
        if ($request->hasFile('file_upload')) {
            if ($document?->file) Storage::disk('public')->delete($document->file);
            $data['file'] = $request->file('file_upload')->store('quality/documents', 'public');
        }
        if ($request->hasFile('logo_upload')) {
            if ($document?->logo && !str_starts_with($document->logo, 'figma/')) Storage::disk('public')->delete($document->logo);
            $data['logo'] = $request->file('logo_upload')->store('quality/logos', 'public');
        }

        return $data;
    }
}
