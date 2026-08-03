<?php

namespace App\Services;

use Illuminate\Http\Request;

class AnalyticsBotDetector
{
    public function inspect(Request $request): array
    {
        $ua = strtolower((string) $request->userAgent());
        $known = preg_match('/bot|crawler|spider|scrapy|curl|wget|python-requests|headless|phantom/', $ua) === 1;
        $missing = $ua === '' || !$request->header('Accept-Language');
        $score = min(100, ($known ? 80 : 0) + ($missing ? 25 : 0));
        return ['is_bot' => $score >= 70, 'score' => $score, 'type' => $known ? 'automated-user-agent' : ($missing ? 'incomplete-client' : 'human')];
    }
}
