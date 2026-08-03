<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnalyticsEventsRequest;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use App\Services\AnalyticsBotDetector;
use App\Services\AnalyticsIpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnalyticsEventController extends Controller
{
    public function __invoke(StoreAnalyticsEventsRequest $request, AnalyticsIpService $ips, AnalyticsBotDetector $bots): JsonResponse
    {
        $data = $request->validated();
        $path = (string) data_get($data, 'events.0.path', '/');
        if (collect(config('analytics.exclude_paths'))->contains(fn ($prefix) => Str::startsWith($path, $prefix))) return response()->json(['accepted' => 0]);

        $ip = $ips->resolve($request); $bot = $bots->inspect($request); $ua = strtolower((string) $request->userAgent());
        $device = str_contains($ua, 'mobile') ? 'mobile' : (str_contains($ua, 'tablet') ? 'tablet' : 'desktop');
        $browser = match (true) { str_contains($ua,'edg/')=>'Edge', str_contains($ua,'firefox')=>'Firefox', str_contains($ua,'chrome')=>'Chrome', str_contains($ua,'safari')=>'Safari', default=>'Otro' };
        $os = match (true) { str_contains($ua,'windows')=>'Windows', str_contains($ua,'android')=>'Android', str_contains($ua,'iphone')||str_contains($ua,'ipad')=>'iOS', str_contains($ua,'mac os')=>'macOS', str_contains($ua,'linux')=>'Linux', default=>'Otro' };
        $url = parse_url((string) $request->header('referer')); $query = []; parse_str($url['query'] ?? '', $query);
        $refHost = $url['host'] ?? null; $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $source = isset($query['utm_source']) ? 'campaign' : (!$refHost || $refHost === $appHost ? 'direct' : (preg_match('/google|bing|yahoo|duckduckgo/', $refHost) ? 'organic' : (preg_match('/facebook|instagram|linkedin|tiktok|youtube|x\.com/', $refHost) ? 'social' : 'referral')));
        $geo = $ips->geolocate($ip);
        $countryCode = strtoupper(substr((string) ($request->header('CF-IPCountry') ?: $geo['country_code']), 0, 2)) ?: null;

        $accepted = DB::transaction(function () use ($data,$request,$ips,$ip,$bot,$device,$browser,$os,$refHost,$source,$query,$countryCode,$geo) {
            $session = AnalyticsSession::firstOrCreate(['session_uuid'=>$data['session_id']], [
                'visitor_hash'=>hash_hmac('sha256',$data['visitor_id'],(string)config('analytics.hash_key')),
                'ip_hash'=>$ips->hash($ip),'ip_version'=>$ips->version($ip),'started_at'=>now(),'last_seen_at'=>now(),
                'landing_path'=>data_get($data,'events.0.path','/'),'referrer_host'=>$refHost,'traffic_source'=>$source,
                'utm_source'=>$query['utm_source']??null,'utm_medium'=>$query['utm_medium']??null,'utm_campaign'=>$query['utm_campaign']??null,
                'utm_content'=>$query['utm_content']??null,'utm_term'=>$query['utm_term']??null,'device_type'=>$device,'browser'=>$browser,'os'=>$os,
                'country_code'=>$countryCode,'country_name'=>$geo['country_name'],'city'=>$geo['city'],
                'latitude'=>$geo['latitude'],'longitude'=>$geo['longitude'],'is_bot'=>$bot['is_bot'],
            ]);
            $count = 0;
            foreach ($data['events'] as $event) {
                $properties = $this->safeProperties($event['properties'] ?? []);
                $created = isset($event['timestamp']) ? now()->parse($event['timestamp'])->clamp(now()->subDay(), now()->addMinute()) : now();
                $record = AnalyticsEvent::firstOrCreate(['event_uuid'=>$event['id']], [
                    'analytics_session_id'=>$session->id,'event_name'=>$event['name'],'path'=>$event['path']??null,
                    'properties'=>$properties,'is_bot'=>$bot['is_bot'],'country_code'=>$countryCode,'created_at'=>$created,
                ]);
                if (!$record->wasRecentlyCreated) continue;
                $count++;
                if ($event['name']==='page_view') DB::table('analytics_page_views')->insert([
                    'analytics_session_id'=>$session->id,'event_uuid'=>$event['id'],'path'=>$event['path']??'/',
                    'title'=>$properties['title']??null,'referrer_path'=>$properties['referrer_path']??null,
                    'duration_seconds'=>(int)($properties['duration_seconds']??0),'max_scroll_depth'=>(int)($properties['scroll_depth']??0),
                    'lcp_ms'=>$properties['lcp_ms']??null,'inp_ms'=>$properties['inp_ms']??null,'cls'=>$properties['cls']??null,'created_at'=>$created,
                ]);
                if ($event['name']==='section_view') DB::table('analytics_section_views')->insert([
                    'analytics_session_id'=>$session->id,'event_uuid'=>$event['id'],'path'=>$event['path']??'/',
                    'section_key'=>(string)($properties['section_key']??'unknown'),'section_order'=>$properties['section_order']??null,
                    'visible_ms'=>(int)($properties['visible_ms']??1500),'max_visibility'=>(int)($properties['max_visibility']??50),
                    'previous_section'=>$properties['previous_section']??null,'next_section'=>$properties['next_section']??null,'created_at'=>$created,
                ]);
                if (in_array($event['name'], ['checkout_started','quote_generated','form_submitted'], true)) DB::table('analytics_conversions')->insert([
                    'analytics_session_id'=>$session->id,'event_uuid'=>$event['id'],'conversion_type'=>$event['name'],
                    'value'=>$properties['value']??null,'currency'=>$properties['currency']??null,'created_at'=>$created,
                ]);
            }
            $session->update(['last_seen_at'=>now(),'exit_path'=>data_get($data,'events.'.(count($data['events'])-1).'.path'),'page_views'=>DB::table('analytics_page_views')->where('analytics_session_id',$session->id)->count()]);
            return $count;
        });
        return response()->json(['accepted'=>$accepted], 202);
    }

    private function safeProperties(array $properties): array
    {
        $allowed = ['title','referrer_path','duration_seconds','scroll_depth','section_key','section_order','visible_ms','max_visibility','previous_section','next_section','target','label','product_id','query_length','results_count','value','currency','metric','rating','delta','lcp_ms','inp_ms','cls'];
        return collect($properties)->only($allowed)->map(fn($v)=>is_string($v)?Str::limit(strip_tags($v),500,''):$v)->all();
    }
}
