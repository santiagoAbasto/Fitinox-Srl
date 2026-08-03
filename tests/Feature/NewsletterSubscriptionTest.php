<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_new_subscription_is_saved_and_confirmed(): void
    {
        $response = $this->from('/')->post('/newsletter/suscribir', [
            'email' => 'CLIENTE@EXAMPLE.COM',
        ]);

        $response->assertRedirect('/')
            ->assertSessionHas('newsletter_notice.title', 'Suscripción confirmada');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'cliente@example.com',
            'is_subscribed' => true,
        ]);
    }

    public function test_an_existing_subscription_gets_a_clear_confirmation_without_resetting_its_date(): void
    {
        $subscribedAt = now()->subMonth()->startOfSecond();
        $subscriber = NewsletterSubscriber::create([
            'email' => 'cliente@example.com',
            'is_subscribed' => true,
            'subscribed_at' => $subscribedAt,
        ]);

        $this->from('/')->post('/newsletter/suscribir', ['email' => $subscriber->email])
            ->assertRedirect('/')
            ->assertSessionHas('newsletter_notice.title', 'Ya estás suscrito');

        $this->assertTrue($subscriber->fresh()->subscribed_at->equalTo($subscribedAt));
    }

    public function test_an_unsubscribed_address_is_reactivated(): void
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'cliente@example.com',
            'is_subscribed' => false,
            'subscribed_at' => now()->subMonths(2),
            'unsubscribed_at' => now()->subMonth(),
            'unsubscribe_source' => 'web',
        ]);

        $this->from('/')->post('/newsletter/suscribir', ['email' => $subscriber->email])
            ->assertRedirect('/')
            ->assertSessionHas('newsletter_notice.title', 'Suscripción reactivada');

        $subscriber->refresh();
        $this->assertTrue($subscriber->is_subscribed);
        $this->assertNull($subscriber->unsubscribed_at);
        $this->assertNull($subscriber->unsubscribe_source);
    }
}
