<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\HtmlSanitizer;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
    public function edit()
    {
        return Inertia::render('About/Edit', [
            'page' => Page::where('slug', 'nosotros')->firstOrFail(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:150'],
            'hero_image' => ['nullable', 'max:255'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'content_title' => ['required', 'max:150'],
            'content' => ['required'],
            'content_image' => ['nullable', 'max:255'],
            'content_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'values_title' => ['required', 'max:150'],
            'mission_title' => ['required', 'max:100'],
            'mission_text' => ['required'],
            'vision_title' => ['required', 'max:100'],
            'vision_text' => ['required'],
            'quality_title' => ['required', 'max:100'],
            'quality_text' => ['required'],
            'video_cover' => ['nullable', 'max:255'],
            'video_cover_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'video_url' => ['nullable', 'max:500'],
            'remove_video' => ['boolean'],
        ]);

        $page = Page::where('slug', 'nosotros')->firstOrFail();
        $sections = $page->sections ?? [];
        foreach (['content_title', 'values_title', 'mission_title', 'mission_text', 'vision_title', 'vision_text', 'quality_title', 'quality_text'] as $key) {
            $sections[$key] = $data[$key];
        }
        $sections['content_image'] = $request->file('content_image_file')?->store('nosotros/content', 'public') ?? $data['content_image'];

        if ($request->boolean('remove_video')) {
            $previousCover = $sections['video_cover'] ?? null;
            unset($sections['video_cover'], $sections['video_url']);
            if (is_string($previousCover) && str_starts_with($previousCover, 'nosotros/video/')) {
                Storage::disk('public')->delete($previousCover);
            }
        } else {
            $sections['video_url'] = $data['video_url'];
            $sections['video_cover'] = $request->file('video_cover_file')?->store('nosotros/video', 'public') ?? $data['video_cover'];
        }

        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('nosotros/hero', 'public') ?? $data['hero_image'],
            'content' => app(HtmlSanitizer::class)->sanitize($data['content']),
            'sections' => $sections,
        ]);

        return back()->with('success', 'La página Nosotros fue actualizada.');
    }

}
