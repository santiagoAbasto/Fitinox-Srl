<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email', 'max:190'],
            'name' => ['nullable', 'max:120'],
        ]);
        $email = strtolower(trim($data['email']));
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber?->is_subscribed) {
            if (! empty($data['name'])) $subscriber->update(['name' => $data['name']]);
            $title = 'Ya estás suscrito';
            $message = 'Este correo ya forma parte del newsletter de Fitinox.';
        } elseif ($subscriber) {
            $subscriber->update([
                'name' => $data['name'] ?? $subscriber->name,
                'is_subscribed' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'unsubscribe_source' => null,
            ]);
            $title = 'Suscripción reactivada';
            $message = 'Volverás a recibir las novedades y actualizaciones de Fitinox.';
        } else {
            NewsletterSubscriber::create([
                'email' => $email,
                'name' => $data['name'] ?? null,
                'is_subscribed' => true,
                'subscribed_at' => now(),
            ]);
            $title = 'Suscripción confirmada';
            $message = 'Gracias por sumarte. Pronto recibirás novedades y actualizaciones de Fitinox.';
        }

        return back()->with('newsletter_notice', compact('title', 'message'));
    }

    public function unsubscribe(Request $request, NewsletterSubscriber $subscriber)
    {
        abort_unless(hash_equals(hash_hmac('sha256', $subscriber->id.'|'.$subscriber->email, config('app.key')), (string) $request->query('signature')), 403);
        $subscriber->update(['is_subscribed' => false, 'unsubscribed_at' => now(), 'unsubscribe_source' => 'web']);

        return response()->view('newsletter.unsubscribed');
    }
}
