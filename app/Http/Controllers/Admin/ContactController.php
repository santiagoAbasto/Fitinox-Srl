<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('Contact/Index', [
            'page' => Page::where('slug', 'contacto')->firstOrFail(),
            'messages' => ContactMessage::latest()->paginate(30)->withQueryString(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function updatePage(Request $request)
    {
        $data = $request->validate([
            'hero_title' => ['required', 'string', 'max:150'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
        ]);
        $page = Page::where('slug', 'contacto')->firstOrFail();
        $page->update([
            'hero_title' => $data['hero_title'],
            'hero_image' => $request->file('hero_image_file')?->store('contact/hero', 'public') ?? $page->hero_image,
        ]);

        return back()->with('success', 'Portada de Contacto guardada correctamente.');
    }

    public function updateDetails(Request $request)
    {
        $data = $request->validate([
            'contact_address' => ['required', 'string', 'max:500'],
            'contact_email' => ['required', 'email', 'max:180'],
            'contact_phone_numbers' => ['required', 'string', 'max:2000', function ($attribute, $value, $fail) {
                if (count($this->toContactList($value)) === 0) {
                    $fail('Ingresá al menos un teléfono.');
                }
            }],
            'contact_whatsapp_numbers' => ['required', 'string', 'max:2000', function ($attribute, $value, $fail) {
                $numbers = $this->toContactList($value);
                if (count($numbers) === 0) {
                    $fail('Ingresá al menos un número de WhatsApp.');
                }
                if (collect($numbers)->contains(fn ($number) => strlen(preg_replace('/\D/', '', $number)) < 8)) {
                    $fail('Cada número de WhatsApp debe incluir código de país y al menos 8 dígitos.');
                }
            }],
            'contact_latitude' => ['required', 'numeric', 'between:-90,90'],
            'contact_longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $phones = $this->toContactList($data['contact_phone_numbers']);
        $whatsapps = $this->toContactList($data['contact_whatsapp_numbers']);
        $settings = [
            'contact_address' => ['value' => $data['contact_address'], 'type' => 'text'],
            'contact_email' => ['value' => $data['contact_email'], 'type' => 'text'],
            'contact_phone_numbers' => ['value' => json_encode($phones), 'type' => 'json'],
            'contact_whatsapp_numbers' => ['value' => json_encode($whatsapps), 'type' => 'json'],
            'contact_latitude' => ['value' => $data['contact_latitude'], 'type' => 'text'],
            'contact_longitude' => ['value' => $data['contact_longitude'], 'type' => 'text'],
            // Keep the established fields in sync for existing footer and page content.
            'address' => ['value' => $data['contact_address'], 'type' => 'text'],
            'email_sales' => ['value' => $data['contact_email'], 'type' => 'text'],
            'phone' => ['value' => implode("\n", $phones), 'type' => 'text'],
            'whatsapp' => ['value' => implode("\n", $whatsapps), 'type' => 'text'],
        ];

        foreach ($settings as $key => $setting) {
            Setting::updateOrCreate(['key' => $key], [
                'group' => 'contact',
                'value' => $setting['value'],
                'type' => $setting['type'],
            ]);
        }

        return back()->with('success', 'Datos públicos de contacto actualizados.');
    }

    public function updateStatus(Request $request, ContactMessage $contactMessage)
    {
        $contactMessage->update($request->validate([
            'status' => ['required', 'in:new,in_progress,replied,closed'],
        ]));

        return back()->with('success', 'Estado del mensaje actualizado.');
    }

    private function toContactList(?string $value): array
    {
        return collect(preg_split('/\R/u', (string) $value) ?: [])
            ->flatMap(function ($line) {
                $numbers = [];

                foreach (preg_split('/\s+\/\s+/u', trim($line)) ?: [] as $part) {
                    $part = trim($part);
                    $digits = preg_replace('/\D/', '', $part);

                    if (strlen($digits) >= 8 || empty($numbers)) {
                        $numbers[] = $part;
                    } else {
                        $numbers[array_key_last($numbers)] .= ' / '.$part;
                    }
                }

                return $numbers;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
