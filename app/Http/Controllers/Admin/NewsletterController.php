<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsletterController extends Controller
{
    public function index()
    {
        return Inertia::render('Newsletter/Index', [
            'subscribers' => NewsletterSubscriber::orderByDesc('is_subscribed')->orderBy('email')->get(),
            'campaigns' => NewsletterCampaign::latest()->take(12)->get(),
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'heading' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:10000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip', 'max:20480'],
            'recipient_ids' => ['nullable', 'array'],
            'recipient_ids.*' => ['integer', 'exists:newsletter_subscribers,id'],
            'send_to_all' => ['nullable', 'boolean'],
        ]);
        $subscribers = NewsletterSubscriber::query()->where('is_subscribed', true)
            ->when(! $request->boolean('send_to_all'), fn ($query) => $query->whereIn('id', $data['recipient_ids'] ?? []))
            ->get();
        abort_if($subscribers->isEmpty(), 422, 'Seleccioná al menos un suscriptor activo.');

        $campaign = NewsletterCampaign::create([
            'subject' => $data['subject'], 'heading' => $data['heading'] ?? null, 'body' => $data['body'],
            'attachment_path' => $request->file('attachment')?->store('newsletter', 'public'),
            'recipient_count' => $subscribers->count(), 'created_by' => $request->user()->id,
        ]);
        $sent = 0; $failed = 0;
        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterMail($campaign, $subscriber));
                $campaign->deliveries()->create(['newsletter_subscriber_id' => $subscriber->id, 'email' => $subscriber->email, 'status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (\Throwable $exception) {
                report($exception);
                $campaign->deliveries()->create(['newsletter_subscriber_id' => $subscriber->id, 'email' => $subscriber->email, 'status' => 'failed', 'error_message' => str($exception->getMessage())->limit(500)]);
                $failed++;
            }
        }
        $campaign->update(['sent_count' => $sent, 'failed_count' => $failed, 'sent_at' => now()]);

        return back()->with('success', "Campaña enviada a {$sent} suscriptor(es).".($failed ? " {$failed} no pudieron recibirla." : ''));
    }

    public function unsubscribe(NewsletterSubscriber $subscriber)
    {
        $subscriber->update(['is_subscribed' => false, 'unsubscribed_at' => now(), 'unsubscribe_source' => 'admin']);
        return back()->with('success', 'Suscriptor dado de baja desde administración.');
    }

    public function resubscribe(NewsletterSubscriber $subscriber)
    {
        $subscriber->update(['is_subscribed' => true, 'subscribed_at' => now(), 'unsubscribed_at' => null, 'unsubscribe_source' => null]);
        return back()->with('success', 'Suscriptor reactivado.');
    }
}
