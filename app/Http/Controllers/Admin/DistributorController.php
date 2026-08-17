<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Page;
use App\Support\OrderCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DistributorController extends Controller
{
    public function edit()
    {
        return Inertia::render('Distributors/Edit', [
            'page' => Page::where('slug', 'distribuidores')->firstOrFail(),
            'distributors' => Distributor::orderBy('sort_order')->orderBy('id')->get(),
            'nextOrder' => OrderCode::next(Distributor::orderByDesc('sort_order')->value('sort_order')),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'max:150'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'distribuidores')->firstOrFail();
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('distributors/hero', 'public') ?? $page->hero_image,
        ]);

        return back()->with('success', 'Portada de Distribuidores guardada correctamente.');
    }

    public function store(Request $request)
    {
        Distributor::create($this->validated($request));
        return back()->with('success', 'Distribuidor agregado correctamente.');
    }

    public function update(Request $request, Distributor $distributor)
    {
        $distributor->update($this->validated($request));
        return back()->with('success', 'Distribuidor actualizado correctamente.');
    }

    public function destroy(Distributor $distributor)
    {
        $distributor->delete();
        return back()->with('success', 'Distribuidor eliminado correctamente.');
    }

    private function validated(Request $request): array
    {
        $this->normalizeCoordinates($request);

        $data = $request->validate([
            'country' => ['required', 'max:100'],
            'name' => ['required', 'max:200'],
            'address' => ['required', 'max:255'],
            'phone' => ['nullable', 'max:150'],
            'email' => ['nullable', 'email', 'max:180'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'sort_order' => ['required', 'string', 'max:3', 'regex:/^[A-Z]+$/'],
            'is_active' => ['required', 'boolean'],
        ], [
            'latitude.required' => 'Ingresá la latitud que muestra Google Maps.',
            'latitude.numeric' => 'La latitud debe contener solamente un número.',
            'latitude.between' => 'La latitud debe estar entre -90 y 90.',
            'longitude.required' => 'Ingresá la longitud que muestra Google Maps.',
            'longitude.numeric' => 'La longitud debe contener solamente un número.',
            'longitude.between' => 'La longitud debe estar entre -180 y 180.',
        ]);

        // The database stores seven decimal places, which provides centimetre-level
        // precision and avoids errors with Google's longer coordinate values.
        $data['latitude'] = round((float) $data['latitude'], 7);
        $data['longitude'] = round((float) $data['longitude'], 7);

        return $data;
    }

    private function normalizeCoordinates(Request $request): void
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Google Maps copies both coordinates together. Allow users to paste
        // that pair into the latitude field and fill the longitude automatically.
        if (is_string($latitude) && (! is_string($longitude) || trim($longitude) === '')) {
            $pair = $this->coordinatePair($latitude);

            if ($pair !== null) {
                [$latitude, $longitude] = $pair;
            }
        }

        $request->merge([
            'latitude' => $this->coordinateValue($latitude),
            'longitude' => $this->coordinateValue($longitude),
        ]);
    }

    /** @return array{0: string, 1: string}|null */
    private function coordinatePair(string $value): ?array
    {
        $value = str_replace(['−', '–', '—'], '-', $value);
        preg_match_all('/[+-]?\d+(?:[.,]\d+)?/', $value, $matches);

        if (count($matches[0]) < 2) {
            return null;
        }

        return [
            str_replace(',', '.', $matches[0][0]),
            str_replace(',', '.', $matches[0][1]),
        ];
    }

    private function coordinateValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim(str_replace(['−', '–', '—'], '-', $value));

        if (preg_match('/^[+-]?\d+(?:[.,]\d+)?$/', $value) === 1) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }
}
