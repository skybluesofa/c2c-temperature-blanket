<?php

namespace App\Apis\GeoNames;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoNames
{
    protected $ttl = 60 * 60 * 24;

    public function get(string $geonameId): array
    {
        return Cache::remember('geoName.'.$geonameId, $this->ttl, function () use ($geonameId) {
            $url = 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/geonames-all-cities-with-a-population-500/records?select=coordinates,timezone&where=geoname_id%3D'.$geonameId.'&limit=1' ?? null;
            $response = Http::get($url)->body();

            $json = json_decode($response, true);
            if (! isset($json['results'][0])) {
                return null;
            }

            return $json['results'][0];
        });
    }
}
