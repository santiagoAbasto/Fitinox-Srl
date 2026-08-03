<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request)
    {
        $days = min(365, max(1, (int)$request->integer('days', 30))); $from = now()->subDays($days-1)->startOfDay();
        $payload = Cache::remember('analytics.dashboard.v2.'.$days, 60, fn()=> $this->payload($from));
        return Inertia::render('Analytics/Index', ['analytics'=>$payload,'filters'=>['days'=>$days],'configured'=>[
            'posthog'=>(bool)config('analytics.posthog.key'),'maxmind'=>is_readable((string)config('analytics.maxmind_database')),'cloudflare'=>(bool)config('services.cloudflare.api_token'),
        ]]);
    }

    private function payload(Carbon $from): array
    {
        $sessions = AnalyticsSession::where('started_at','>=',$from); $human = (clone $sessions)->where('is_bot',false);
        $sessionCount=(clone $human)->count(); $views=DB::table('analytics_page_views')->where('created_at','>=',$from)->count();
        $single=(clone $human)->where('page_views','<=',1)->count(); $conversions=DB::table('analytics_conversions')->where('created_at','>=',$from)->count();
        $series=DB::table('analytics_sessions')->selectRaw('DATE(started_at) as date, COUNT(*) sessions, COUNT(DISTINCT visitor_hash) visitors')->where('started_at','>=',$from)->where('is_bot',false)->groupByRaw('DATE(started_at)')->orderBy('date')->get();
        $sources=(clone $human)->selectRaw('traffic_source name, COUNT(*) value')->groupBy('traffic_source')->orderByDesc('value')->get();
        $pages=DB::table('analytics_page_views')->selectRaw('path name, COUNT(*) views')->where('created_at','>=',$from)->groupBy('path')->orderByDesc('views')->limit(10)->get();
        $sections=DB::table('analytics_section_views')->selectRaw('section_key name, COUNT(*) views, ROUND(AVG(visible_ms)) avg_ms')->where('created_at','>=',$from)->groupBy('section_key')->orderByDesc('views')->limit(10)->get();
        $events=AnalyticsEvent::where('created_at','>=',$from)->latest('created_at')->limit(30)->get(['event_uuid','event_name','path','country_code','is_bot','created_at']);
        $devices=(clone $human)->selectRaw('device_type name, COUNT(*) value')->groupBy('device_type')->get();
        $countries=(clone $human)->selectRaw('country_code code, country_name name, latitude, longitude, COUNT(*) sessions')->whereNotNull('country_code')->groupBy('country_code','country_name','latitude','longitude')->get();
        return ['kpis'=>[
            'sessions'=>$sessionCount,'visitors'=>(clone $human)->distinct('visitor_hash')->count('visitor_hash'),'pageViews'=>$views,
            'pagesPerSession'=>$sessionCount?round($views/$sessionCount,2):0,'avgDuration'=>(int)((clone $human)->avg('duration_seconds')?:0),
            'bounceRate'=>$sessionCount?round($single/$sessionCount*100,1):0,'conversions'=>$conversions,'conversionRate'=>$sessionCount?round($conversions/$sessionCount*100,1):0,
            'bots'=>(clone $sessions)->where('is_bot',true)->count(),'activeNow'=>AnalyticsSession::where('last_seen_at','>=',now()->subMinutes(5))->where('is_bot',false)->count(),
        ],'series'=>$series->values()->all(),'sources'=>$sources->values()->all(),'pages'=>$pages->values()->all(),
            'sections'=>$sections->values()->all(),'devices'=>$devices->values()->all(),'countries'=>$countries->values()->all(),
            'events'=>$events->values()->all()];
    }
}
