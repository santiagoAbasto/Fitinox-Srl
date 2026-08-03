<?php

namespace Tests\Feature;

use App\Services\AnalyticsBotDetector;
use App\Services\AnalyticsIpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_a_session_page_view_and_prevents_duplicates(): void
    {
        $session=(string)Str::uuid(); $event=(string)Str::uuid();
        $payload=['session_id'=>$session,'visitor_id'=>'anonymous-visitor','events'=>[['id'=>$event,'name'=>'page_view','path'=>'/productos','properties'=>['title'=>'Productos','password'=>'never-store']]]];
        $this->withHeaders(['User-Agent'=>'Mozilla/5.0 Chrome/125','Accept-Language'=>'es','CF-Connecting-IP'=>'203.0.113.20'])->postJson('/analytics/events',$payload)->assertAccepted()->assertJson(['accepted'=>1]);
        $this->postJson('/analytics/events',$payload)->assertAccepted()->assertJson(['accepted'=>0]);
        $this->assertDatabaseCount('analytics_sessions',1); $this->assertDatabaseCount('analytics_page_views',1);
        $this->assertDatabaseMissing('analytics_events',['properties'=>'never-store']);
    }

    public function test_it_rejects_unknown_events_and_html_paths(): void
    {
        $base=['session_id'=>(string)Str::uuid(),'visitor_id'=>'v','events'=>[['id'=>(string)Str::uuid(),'name'=>'execute_sql','path'=>'/']]];
        $this->assertSame(422, $this->postJson('/analytics/events',$base)->getStatusCode());
        $base['events'][0]['name']='page_view'; $base['events'][0]['path']='<script>';
        $this->assertSame(422, $this->postJson('/analytics/events',$base)->getStatusCode());
    }

    public function test_ip_is_hashed_masked_and_cloudflare_header_wins(): void
    {
        $request=Request::create('/','GET',[],[],[],['REMOTE_ADDR'=>'10.0.0.1','HTTP_CF_CONNECTING_IP'=>'2001:db8::1']);
        $service=app(AnalyticsIpService::class); $ip=$service->resolve($request);
        $this->assertSame('2001:db8::1',$ip); $this->assertSame('ipv6',$service->version($ip));
        $this->assertNotSame($ip,$service->hash($ip)); $this->assertStringEndsWith('::',$service->mask($ip));
    }

    public function test_bot_detection_uses_multiple_signals(): void
    {
        $request=Request::create('/','GET',[],[],[],['HTTP_USER_AGENT'=>'curl/8.0']);
        $result=app(AnalyticsBotDetector::class)->inspect($request);
        $this->assertTrue($result['is_bot']); $this->assertGreaterThanOrEqual(70,$result['score']);
    }

    public function test_maxmind_resolves_only_public_ips(): void
    {
        $service=app(AnalyticsIpService::class);
        $public=$service->geolocate('8.8.8.8');
        $this->assertSame('US',$public['country_code']);
        $this->assertNotNull($public['latitude']);
        $this->assertNull($service->geolocate('127.0.0.1')['country_code']);
        $this->assertTrue($service->isPrivate('10.0.0.1'));
    }
}
