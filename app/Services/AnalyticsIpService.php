<?php

namespace App\Services;

use GeoIp2\Database\Reader;
use Illuminate\Http\Request;
use MaxMind\Db\Reader\InvalidDatabaseException;

class AnalyticsIpService
{
    public function resolve(Request $request): ?string
    {
        $candidate = $request->header('CF-Connecting-IP') ?: $request->ip();
        return filter_var($candidate, FILTER_VALIDATE_IP) ? $candidate : null;
    }

    public function hash(?string $ip): ?string
    {
        return $ip ? hash_hmac('sha256', $ip, (string) config('analytics.hash_key')) : null;
    }

    public function mask(?string $ip): ?string
    {
        if (!$ip) return null;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { $p = explode('.', $ip); return "$p[0].$p[1].x.x"; }
        $p = explode(':', $ip); return implode(':', array_slice($p, 0, 3)).'::';
    }

    public function version(?string $ip): ?string
    {
        if (!$ip) return null;
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 'ipv4' : 'ipv6';
    }

    /** @return array{country_code:?string,country_name:?string,city:?string,latitude:?float,longitude:?float} */
    public function geolocate(?string $ip): array
    {
        $empty = ['country_code'=>null,'country_name'=>null,'city'=>null,'latitude'=>null,'longitude'=>null];
        $database = config('analytics.maxmind_database');
        if (!$ip || !$database || !is_readable($database) || $this->isPrivate($ip)) return $empty;

        try {
            $record = (new Reader($database))->city($ip);
            return [
                'country_code'=>$record->country->isoCode,
                'country_name'=>$record->country->name,
                'city'=>$record->city->name,
                'latitude'=>$record->location->latitude,
                'longitude'=>$record->location->longitude,
            ];
        } catch (\Throwable $exception) {
            if ($exception instanceof InvalidDatabaseException) report($exception);
            return $empty;
        }
    }

    public function isPrivate(?string $ip): bool
    {
        return !$ip || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
