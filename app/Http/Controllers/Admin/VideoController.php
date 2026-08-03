<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Video;
use App\Support\OrderCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function edit()
    {
        return Inertia::render('Videos/Edit', [
            'page' => Page::where('slug', 'videos')->firstOrFail(),
            'videos' => Video::orderBy('sort_order')->orderBy('id')->get(),
            'nextOrder' => OrderCode::next(Video::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:150'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'videos')->firstOrFail();
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('videos/hero', 'public') ?? $page->hero_image,
        ]);

        return back()->with('success', 'Portada de Videos guardada correctamente.');
    }

    public function store(Request $request)
    {
        Video::create($this->validated($request));

        return back()->with('success', 'Video agregado correctamente.');
    }

    public function update(Request $request, Video $video)
    {
        $video->update($this->validated($request, $video));

        return back()->with('success', 'Video actualizado correctamente.');
    }

    public function destroy(Video $video)
    {
        foreach (['thumbnail', 'video_path'] as $field) {
            if ($video->{$field} && !str_starts_with($video->{$field}, 'figma/')) Storage::disk('public')->delete($video->{$field});
        }
        $video->delete();

        return back()->with('success', 'Video eliminado correctamente.');
    }

    private function validated(Request $request, ?Video $video = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'max:200'],
            'media_type' => ['required', 'in:youtube,video'],
            'youtube_url' => ['nullable', 'url', 'max:500', 'required_if:media_type,youtube'],
            'video_upload' => [$video ? 'nullable' : 'required_if:media_type,video', 'file', 'mimes:mp4,webm,mov', 'max:204800'],
            'thumbnail_upload' => [$video ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
            'is_active' => ['required', 'boolean'],
        ]);
        unset($data['video_upload'], $data['thumbnail_upload']);
        if ($request->hasFile('thumbnail_upload')) {
            if ($video?->thumbnail && !str_starts_with($video->thumbnail, 'figma/')) Storage::disk('public')->delete($video->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail_upload')->store('videos/thumbnails', 'public');
        }
        if ($request->hasFile('video_upload')) {
            if ($video?->video_path) Storage::disk('public')->delete($video->video_path);
            $data['video_path'] = $request->file('video_upload')->store('videos/files', 'public');
        }
        if ($data['media_type'] === 'youtube') {
            if ($video?->video_path) Storage::disk('public')->delete($video->video_path);
            $data['video_path'] = null;
        }
        if ($data['media_type'] === 'video') $data['youtube_url'] = null;

        return $data;
    }
}
