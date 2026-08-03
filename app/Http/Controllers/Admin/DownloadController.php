<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Models\Page;
use App\Support\OrderCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DownloadController extends Controller
{
    public function edit()
    {
        return Inertia::render('Downloads/Edit', [
            'page' => Page::where('slug', 'descargas')->firstOrFail(),
            'downloads' => Download::orderBy('sort_order')->orderBy('id')->get(),
            'nextOrder' => OrderCode::next(Download::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:150'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'descargas')->firstOrFail();
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('downloads/hero', 'public') ?? $page->hero_image,
        ]);

        return back()->with('success', 'Portada de Descargas actualizada.');
    }

    public function store(Request $request)
    {
        Download::create($this->validated($request));

        return back()->with('success', 'Archivo agregado a Descargas.');
    }

    public function update(Request $request, Download $download)
    {
        $download->update($this->validated($request, $download));

        return back()->with('success', 'Descarga actualizada.');
    }

    public function destroy(Download $download)
    {
        if ($download->file) Storage::disk('public')->delete($download->file);
        $download->delete();

        return back()->with('success', 'Descarga eliminada.');
    }

    private function validated(Request $request, ?Download $download = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'max:180'],
            'file_upload' => [$download ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,odt,ods,odp,rtf,txt,jpg,jpeg,png,webp', 'max:51200'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
            'is_active' => ['required', 'boolean'],
        ]);
        unset($data['file_upload']);
        if ($request->hasFile('file_upload')) {
            if ($download?->file) Storage::disk('public')->delete($download->file);
            $file = $request->file('file_upload');
            $data['file'] = $file->store('downloads/files', 'public');
            $data['format'] = strtoupper($file->getClientOriginalExtension());
            $data['file_size'] = $this->sizeLabel($file->getSize());
        } elseif ($download) {
            $data['file'] = $download->file;
            $data['format'] = $download->format;
            $data['file_size'] = $download->file_size;
        } else {
            $data['file'] = '';
            $data['format'] = '';
            $data['file_size'] = '';
        }
        $data['online_url'] = null;

        return $data;
    }

    private function sizeLabel(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1, ',', '').' MB'
            : number_format(max(1, $bytes / 1024), 0, ',', '').' KB';
    }
}
