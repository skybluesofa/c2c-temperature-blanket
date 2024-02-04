<?php

namespace App\Apis\ColorNames;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ColorNames
{
    protected $ttl = 60 * 60 * 24;

    public function get(string $hex): string
    {
        return Cache::remember('color-name.'.$hex, $this->ttl, function () use ($hex) {
            return Http::get('https://colornames.org/search/json/?hex='.$hex)['name'] ?? $hex;
        });
    }
}
