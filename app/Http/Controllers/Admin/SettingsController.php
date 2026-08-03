<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = Setting::pluck('value', 'key');
        $home = Page::where('slug', 'inicio')->first();
        $fallbackImage = data_get($home?->sections, 'header_logo') ?: 'figma/header-footer/header-logo.png';

        return Inertia::render('Settings/Edit', [
            'settings' => $settings,
            'seo' => [
                'customImageUrl' => filled($settings['seo_og_image'] ?? null)
                    ? Storage::url($settings['seo_og_image'])
                    : null,
                'fallbackImageUrl' => Storage::url($fallbackImage),
                'fallbackSource' => filled(data_get($home?->sections, 'header_logo'))
                    ? 'logo configurado en el header'
                    : 'logo institucional del header',
            ],
            'seoPages' => Page::orderBy('sort_order')->get()->map(function (Page $page) {
                $imagePath = data_get($page->sections, 'seo_og_image');
                $publicPaths = [
                    'inicio' => '/',
                    'nosotros' => '/nosotros',
                    'descargas' => '/descargas',
                    'calidad' => '/calidad',
                    'videos' => '/videos',
                    'distribuidores' => '/distribuidores',
                    'presupuesto' => '/presupuesto',
                    'contacto' => '/contacto',
                ];

                return [
                    'id' => $page->id,
                    'label' => $page->title,
                    'slug' => $page->slug,
                    'publicUrl' => $publicPaths[$page->slug] ?? "/{$page->slug}",
                    'seoTitle' => $page->seo_title,
                    'seoDescription' => $page->seo_description,
                    'seoKeywords' => data_get($page->sections, 'seo_keywords'),
                    'ogImageUrl' => filled($imagePath) ? Storage::url($imagePath) : null,
                ];
            })->values(),
            'users' => User::query()->whereIn('role', ['super_admin', 'admin', 'editor'])->orderBy('name')->get(['id', 'name', 'email', 'role', 'is_active', 'created_at']),
            'canManageAdministrators' => auth()->user()?->isSuperAdmin() ?? false,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'seo_title' => ['required', 'max:70'],
            'seo_description' => ['required', 'max:170'],
            'seo_keywords' => ['nullable', 'max:255'],
            'seo_og_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_seo_og_image' => ['nullable', 'boolean'],
            'whatsapp_enabled' => ['required', 'boolean'],
            'whatsapp_number' => ['required', 'regex:/^[0-9]{8,20}$/'],
            'whatsapp_message' => ['nullable', 'max:300'],
            'whatsapp_position' => ['required', Rule::in(['right', 'left'])],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:2048'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($request->boolean('remove_seo_og_image')) {
            $currentImage = Setting::where('key', 'seo_og_image')->value('value');

            if (filled($currentImage)) {
                Storage::disk('public')->delete($currentImage);
            }

            $data['seo_og_image'] = '';
        }

        if ($request->hasFile('seo_og_image_file')) {
            $data['seo_og_image'] = $request->file('seo_og_image_file')->store('settings/seo', 'public');
        }
        unset($data['seo_og_image_file'], $data['remove_seo_og_image']);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], [
                'group' => str_starts_with($key, 'seo_')
                    ? 'seo'
                    : (str_starts_with($key, 'contact_')
                        ? 'contact'
                        : (str_ends_with($key, '_url') ? 'social' : 'whatsapp')),
                'value' => is_bool($value) ? ($value ? '1' : '0') : $value,
                'type' => is_bool($value) ? 'boolean' : 'text',
            ]);
        }

        return back()->with('success', 'Configuración global actualizada.');
    }

    public function updatePageSeo(Request $request, Page $page)
    {
        $data = $request->validate([
            'seo_title' => ['nullable', 'max:70'],
            'seo_description' => ['nullable', 'max:170'],
            'seo_keywords' => ['nullable', 'max:255'],
            'seo_og_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_seo_og_image' => ['nullable', 'boolean'],
        ]);

        $sections = $page->sections ?? [];
        $currentImage = $sections['seo_og_image'] ?? null;

        if ($request->boolean('remove_seo_og_image')) {
            if (filled($currentImage)) {
                Storage::disk('public')->delete($currentImage);
            }

            unset($sections['seo_og_image']);
        }

        if ($request->hasFile('seo_og_image_file')) {
            if (filled($currentImage)) {
                Storage::disk('public')->delete($currentImage);
            }

            $sections['seo_og_image'] = $request->file('seo_og_image_file')->store("pages/{$page->slug}/seo", 'public');
        }

        if (filled($data['seo_keywords'] ?? null)) {
            $sections['seo_keywords'] = $data['seo_keywords'];
        } else {
            unset($sections['seo_keywords']);
        }

        $page->update([
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'sections' => $sections,
        ]);

        return back()->with('success', "SEO de {$page->title} actualizado.");
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'min:8', 'max:100'],
            'role' => ['required', Rule::in(['admin', 'editor'])],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresá un correo electrónico válido.',
            'email.unique' => 'Ya existe un usuario con este correo electrónico.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'role.required' => 'Seleccioná un rol para el usuario.',
        ]);
        abort_unless($request->user()?->isSuperAdmin(), 403);
        User::create([...$data, 'is_active' => true]);

        return back()->with('success', 'Usuario administrativo creado.');
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);
        abort_unless(in_array($user->role, ['super_admin', 'admin', 'editor'], true), 404);
        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'min:8', 'max:100'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'editor'])],
            'is_active' => ['required', 'boolean'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresá un correo electrónico válido.',
            'email.unique' => 'Ya existe un usuario con este correo electrónico.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'role.required' => 'Seleccioná un rol para el usuario.',
        ]);
        if ($user->isSuperAdmin() && (! $data['is_active'] || $data['role'] !== 'super_admin')) {
            abort(422, 'La cuenta Super Admin no puede desactivarse ni perder su rol.');
        }
        if ($request->user()->is($user) && ! $data['is_active']) abort(422, 'No podés desactivar tu propia cuenta.');
        if (blank($data['password'])) unset($data['password']);
        $user->update($data);

        return back()->with('success', 'Usuario administrativo actualizado.');
    }
}
