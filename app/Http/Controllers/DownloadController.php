<?php

namespace App\Http\Controllers;

use App\Models\Download;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function index()
    {
        return view('downloads.index', [
            'page' => Page::where('slug', 'descargas')->where('is_published', true)->firstOrFail(),
            'downloads' => Download::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function view(Download $download)
    {
        abort_unless($download->is_active, 404);
        abort_unless($download->file && Storage::disk('public')->exists($download->file), 404);

        $extension = strtolower(pathinfo($download->file, PATHINFO_EXTENSION));
        $office = in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf'], true);
        $host = request()->getHost();

        return view('downloads.viewer', [
            'download' => $download,
            'extension' => $extension,
            'office' => $office,
            'isLocal' => in_array($host, ['127.0.0.1', 'localhost', '::1'], true),
            'rawUrl' => route('downloads.raw', $download),
            'downloadUrl' => route('downloads.file', $download),
        ]);
    }

    public function raw(Download $download)
    {
        abort_unless($download->is_active && $download->file && Storage::disk('public')->exists($download->file), 404);
        $path = Storage::disk('public')->path($download->file);

        return response()->file($path, [
            'Content-Type' => Storage::disk('public')->mimeType($download->file) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.basename($download->file).'"',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function download(Download $download)
    {
        abort_unless($download->is_active && $download->file && Storage::disk('public')->exists($download->file), 404);

        return Storage::disk('public')->download($download->file);
    }
}
