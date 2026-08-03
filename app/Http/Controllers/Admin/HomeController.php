<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\HtmlSanitizer;

class HomeController extends Controller
{
    public function edit()
    {
        return Inertia::render('Home/Edit', [
            'page' => Page::where('slug', 'inicio')->firstOrFail(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:255'],
            'hero_image' => ['nullable', 'max:255'],
            'hero_image_file' => ['nullable', 'image', 'max:8192'],
            'header_logo' => ['nullable', 'max:255'],
            'header_logo_file' => ['nullable', 'image', 'max:4096'],
            'hero_button_label' => ['required', 'max:60'],
            'nav_about' => ['required', 'max:40'],
            'nav_products' => ['required', 'max:40'],
            'nav_downloads' => ['required', 'max:40'],
            'nav_quality' => ['required', 'max:40'],
            'nav_videos' => ['required', 'max:40'],
            'nav_distributors' => ['required', 'max:40'],
            'nav_quote' => ['required', 'max:40'],
            'nav_contact' => ['required', 'max:40'],
            'products_title' => ['required', 'max:100'],
            'products_button_label' => ['required', 'max:60'],
            'products_button_url' => ['nullable', 'string', 'max:500', 'regex:/^(\/|https?:\/\/|#)/'],
            'about_title' => ['required', 'max:150'],
            'about_image' => ['nullable', 'max:255'],
            'about_image_file' => ['nullable', 'image', 'max:8192'],
            'about_body' => ['required'],
            'about_button_label' => ['required', 'max:60'],
            'about_button_url' => ['nullable', 'string', 'max:500', 'regex:/^(\/|https?:\/\/|#)/'],
            'featured_title' => ['required', 'max:150'],
            'industry_title' => ['required', 'max:150'],
            'industry_image' => ['nullable', 'max:255'],
            'industry_image_file' => ['nullable', 'image', 'max:8192'],
            'industry_text' => ['required'],
            'industry_button_label' => ['required', 'max:60'],
            'newsletter_title' => ['required', 'max:150'],
            'social_title' => ['required', 'max:100'],
            'client_label' => ['required', 'max:40'],
            'copyright' => ['required', 'max:255'],
            'address' => ['required'],
            'email_sales' => ['required', 'email'],
            'email_quality' => ['required', 'email'],
            'email_warehouse' => ['required', 'email'],
            'email_admin' => ['required', 'email'],
            'phone' => ['required'],
            'whatsapp' => ['required'],
        ]);

        $page = Page::where('slug', 'inicio')->firstOrFail();
        $data['hero_image'] = $request->file('hero_image_file')?->store('home/hero', 'public') ?? $data['hero_image'];
        $data['header_logo'] = $request->file('header_logo_file')?->store('home/header', 'public') ?? $data['header_logo'];
        $data['about_image'] = $request->file('about_image_file')?->store('home/about', 'public') ?? $data['about_image'];
        $data['industry_image'] = $request->file('industry_image_file')?->store('home/industry', 'public') ?? $data['industry_image'];
        $sanitizer = app(HtmlSanitizer::class);
        $data['about_body'] = $sanitizer->sanitize($data['about_body']);
        $data['industry_text'] = $sanitizer->sanitize($data['industry_text']);
        $sections = array_merge($page->sections ?? [], collect($data)->except([
            'hero_title', 'hero_image', 'about_body', 'address', 'email_sales', 'email_quality',
            'email_warehouse', 'email_admin', 'phone', 'whatsapp', 'copyright',
            'hero_image_file', 'header_logo_file', 'about_image_file', 'industry_image_file',
        ])->all());

        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $data['hero_image'],
            'content' => $data['about_body'],
            'sections' => $sections,
        ]);

        foreach (['address', 'email_sales', 'email_quality', 'email_warehouse', 'email_admin', 'phone', 'whatsapp', 'copyright'] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $data[$key], 'group' => 'contact']);
        }

        return back()->with('success', 'La Home fue actualizada correctamente.');
    }

    public function about()
    {
        return Inertia::render('Home/About', [
            'page' => Page::where('slug', 'inicio')->firstOrFail(),
        ]);
    }

    public function updateAbout(Request $request)
    {
        $data = $request->validate([
            'about_title' => ['required', 'max:150'],
            'about_body' => ['required'],
            'about_button_label' => ['required', 'max:60'],
            'about_button_url' => ['nullable', 'string', 'max:500', 'regex:/^(\/|https?:\/\/|#)/'],
            'about_image' => ['nullable', 'max:255'],
            'about_image_file' => ['nullable', 'image', 'max:8192'],
        ]);

        $page = Page::where('slug', 'inicio')->firstOrFail();
        $sections = $page->sections ?? [];
        $sections['about_title'] = $data['about_title'];
        $sections['about_button_label'] = $data['about_button_label'];
        $sections['about_button_url'] = ($data['about_button_url'] ?? null) ?: '/nosotros';
        $sections['about_image'] = $request->file('about_image_file')?->store('home/about', 'public') ?? $data['about_image'];

        $page->update([
            'content' => app(HtmlSanitizer::class)->sanitize($data['about_body']),
            'sections' => $sections,
        ]);

        return back()->with('success', 'La sección Nosotros fue actualizada.');
    }

    public function industry()
    {
        return Inertia::render('Home/Industry', [
            'page' => Page::where('slug', 'inicio')->firstOrFail(),
        ]);
    }

    public function updateIndustry(Request $request)
    {
        $data = $request->validate([
            'industry_title' => ['required', 'max:150'],
            'industry_text' => ['required'],
            'industry_button_label' => ['required', 'max:60'],
            'industry_button_url' => ['required', 'max:500'],
            'industry_image' => ['nullable', 'max:255'],
            'industry_image_file' => ['nullable', 'image', 'max:8192'],
            'industry_is_active' => ['required', 'boolean'],
        ]);

        $page = Page::where('slug', 'inicio')->firstOrFail();
        $sections = $page->sections ?? [];
        $sections['industry_title'] = $data['industry_title'];
        $sections['industry_text'] = app(HtmlSanitizer::class)->sanitize($data['industry_text']);
        $sections['industry_button_label'] = $data['industry_button_label'];
        $sections['industry_button_url'] = $data['industry_button_url'];
        $sections['industry_is_active'] = $data['industry_is_active'];
        $sections['industry_image'] = $request->file('industry_image_file')?->store('home/industry', 'public') ?? $data['industry_image'];

        $page->update(['sections' => $sections]);

        return back()->with('success', 'El banner Oil & Gas fue actualizado.');
    }

}
