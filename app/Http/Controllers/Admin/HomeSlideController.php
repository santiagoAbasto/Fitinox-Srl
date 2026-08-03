<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HomeSlideController extends Controller
{
    public function index()
    {
        return Inertia::render('Home/Sliders', [
            'slides' => HomeSlide::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store()
    {
        $slide = HomeSlide::create([
            'title' => 'Nuevo slide',
            'button_label' => 'VER PRODUCTOS',
            'button_url' => '#productos',
            'media_type' => 'image',
            'is_active' => true,
            'sort_order' => $this->nextOrderCode(),
        ]);

        return redirect("/admin/home/sliders#slide-{$slide->id}")->with('success', 'Slide creado. Completá su contenido.');
    }

    public function update(Request $request, HomeSlide $slide)
    {
        $data = $request->validate([
            'title' => ['required', 'max:255'],
            'button_label' => ['nullable', 'max:60'],
            'button_url' => ['nullable', 'max:255'],
            'media_type' => ['required', 'in:image,video,youtube'],
            'youtube_url' => ['nullable', 'url', 'max:500', 'required_if:media_type,youtube'],
            'media_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm,mov', 'max:51200'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
        ]);

        if ($data['media_type'] !== 'youtube' && ! $request->hasFile('media_file') && (! $slide->media_path || $data['media_type'] !== $slide->media_type)) {
            return back()->withErrors(['media_file' => 'Seleccioná una imagen o un video para este slide.']);
        }

        if ($request->hasFile('media_file')) {
            if ($slide->media_path && str_starts_with($slide->media_path, 'home/slides/')) {
                Storage::disk('public')->delete($slide->media_path);
            }
            $data['media_path'] = $request->file('media_file')->store('home/slides', 'public');
        }

        unset($data['media_file']);
        $slide->update($data);

        return back()->with('success', 'Slide actualizado.');
    }

    public function destroy(HomeSlide $slide)
    {
        if ($slide->media_path && str_starts_with($slide->media_path, 'home/slides/')) {
            Storage::disk('public')->delete($slide->media_path);
        }
        $slide->delete();

        return back()->with('success', 'Slide eliminado.');
    }

    private function nextOrderCode(): string
    {
        $current = HomeSlide::orderByDesc('sort_order')->value('sort_order');
        if (! $current) {
            return 'A';
        }

        if (strlen($current) === 1) {
            return $current.'A';
        }

        if ($current[1] !== 'Z') {
            return $current[0].chr(ord($current[1]) + 1);
        }

        return chr(ord($current[0]) + 1);
    }
}
