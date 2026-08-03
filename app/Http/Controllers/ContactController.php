<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Setting;
use App\Mail\ContactMessageMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Services\TurnstileService;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact.index', [
            'page' => Page::where('slug', 'contacto')->where('is_published', true)->firstOrFail(),
            'settings' => Setting::pluck('value', 'key'),
        ]);
    }

    public function store(Request $request, TurnstileService $turnstile)
    {
        $turnstile->validate($request, 'contact');
        $message = ContactMessage::create($request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
        ]));
        $recipient = Setting::where('key', 'contact_email')->value('value') ?: Setting::where('key', 'email_sales')->value('value');
        if ($recipient) {
            try { Mail::to($recipient)->send(new ContactMessageMail($message)); } catch (\Throwable $exception) { report($exception); }
        }

        return back()->with('success', 'Tu mensaje fue enviado correctamente. El equipo de Fitinox se pondrá en contacto.');
    }
}
