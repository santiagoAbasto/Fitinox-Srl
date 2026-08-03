<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\QualityDocument;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class QualityController extends Controller
{
    public function index()
    {
        return view('quality.index', [
            'page' => Page::where('slug', 'calidad')->where('is_published', true)->firstOrFail(),
            'documents' => QualityDocument::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function download(QualityDocument $document)
    {
        abort_unless($document->is_active && $document->file && Storage::disk('public')->exists($document->file), 404);

        return Storage::disk('public')->download($document->file);
    }
}
