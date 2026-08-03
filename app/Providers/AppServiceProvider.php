<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('analytics', function (Request $request) {
            $session = (string) $request->input('session_id', 'anonymous');
            return [Limit::perMinute(120)->by($request->ip()), Limit::perMinute(60)->by($session)];
        });

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(10)->by('login-ip|'.$request->ip()),
            Limit::perMinute(5)->by('login-id|'.$this->identityKey($request).'|'.$request->ip()),
        ]);

        RateLimiter::for('registration', fn (Request $request) => [
            Limit::perHour(20)->by('registration-ip|'.$request->ip()),
            Limit::perHour(3)->by('registration-id|'.$this->identityKey($request)),
        ]);
        RateLimiter::for('public-form', fn (Request $request) => [
            Limit::perMinute(10)->by('public-form|'.$request->route()?->getName().'|'.$request->ip()),
            Limit::perHour(30)->by('public-form-hour|'.$request->ip()),
        ]);
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(60)->by('search|'.$request->ip()));
        RateLimiter::for('upload', fn (Request $request) => [
            Limit::perMinute(10)->by('upload-user|'.($request->user()?->id ?: $request->ip())),
            Limit::perHour(60)->by('upload-ip|'.$request->ip()),
        ]);
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(5)->by('checkout|'.($request->user()?->id ?: $request->ip())));
    }

    private function identityKey(Request $request): string
    {
        $identity = Str::lower(trim((string) ($request->input('login') ?: $request->input('email'))));
        return hash('sha256', $identity);
    }
}
