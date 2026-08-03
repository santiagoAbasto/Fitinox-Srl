<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('analytics:summarize {--date=}', function () {
    $date = $this->option('date') ?: now()->subDay()->toDateString();
    $start = now()->parse($date)->startOfDay(); $end = $start->copy()->endOfDay();
    $sessions = DB::table('analytics_sessions')->whereBetween('started_at', [$start,$end]);
    $values = [
        'sessions'=>(clone $sessions)->where('is_bot',false)->count(),
        'visitors'=>(clone $sessions)->where('is_bot',false)->distinct('visitor_hash')->count('visitor_hash'),
        'page_views'=>DB::table('analytics_page_views')->whereBetween('created_at',[$start,$end])->count(),
        'events'=>DB::table('analytics_events')->whereBetween('created_at',[$start,$end])->count(),
        'conversions'=>DB::table('analytics_conversions')->whereBetween('created_at',[$start,$end])->count(),
        'bots'=>(clone $sessions)->where('is_bot',true)->count(),
        'duration_seconds'=>(int)((clone $sessions)->sum('duration_seconds')),
    ];
    DB::table('analytics_daily_summaries')->updateOrInsert(['date'=>$date,'dimension'=>'all','dimension_value'=>'all'], $values+['updated_at'=>now(),'created_at'=>now()]);
    $this->info("Resumen analítico generado para {$date}.");
})->purpose('Agrega las métricas de analítica del día indicado');

Artisan::command('analytics:prune', function () {
    $before = now()->subDays((int)config('analytics.retention_days'));
    DB::table('analytics_sessions')->where('last_seen_at','<',$before)->delete();
    DB::table('analytics_blocked_requests')->where('created_at','<',$before)->delete();
    DB::table('analytics_bot_events')->where('created_at','<',$before)->delete();
    $this->info('Datos analíticos fuera de retención eliminados.');
})->purpose('Aplica la retención configurada');

Schedule::command('analytics:summarize')->dailyAt('00:20')->withoutOverlapping();
Schedule::command('analytics:prune')->dailyAt('02:10')->withoutOverlapping();
