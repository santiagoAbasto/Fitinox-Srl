<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:190'], 'name' => ['nullable', 'max:120']]);
        NewsletterSubscriber::updateOrCreate(['email' => strtolower($data['email'])], [
            'name' => $data['name'] ?? null,
            'is_subscribed' => true,
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'unsubscribe_source' => null,
        ]);

        return back()->with('success', 'Te suscribiste correctamente al newsletter de Fitinox.');
    }

    public function unsubscribe(Request $request, NewsletterSubscriber $subscriber)
    {
        abort_unless(hash_equals(hash_hmac('sha256', $subscriber->id.'|'.$subscriber->email, config('app.key')), (string) $request->query('signature')), 403);
        $subscriber->update(['is_subscribed' => false, 'unsubscribed_at' => now(), 'unsubscribe_source' => 'web']);

        return response()->view('newsletter.unsubscribed');
    }
}
