<?php

namespace App\Apis\TemperatureBlanketDotCom;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class TemperatureBlanketDotCom
{
    protected int $cacheTime = 60 * 60 * 24;

    public function generate(string $tbdcScheme = 'default'): ?array
    {
        $config = $this->parseUrl($tbdcScheme);

        return [
            'latitude' => $this->generateCoordinates('lat', $config),
            'longitude' => $this->generateCoordinates('lon', $config),
            'timezone' => $this->generateTimezone($config),
            'design' => $this->generateDesign($config),
            'colors' => [
                'temperature' => $this->generateColors('temp', $config),
                'daylight' => $this->generateColors('dayt', $config),
                'precipitation' => [
                    'snow' => $this->generateColors('snow', $config),
                    'rain' => $this->generateColors('prcp', $config),
                ],
            ],
        ];
    }

    protected function parseUrl(string $tbdcScheme = 'default'): ?array
    {
        if (empty($url = $this->getConfig($tbdcScheme))) {
            return null;
        }

        $config = [];
        parse_str(parse_url($url)['fragment'], $config);

        return [
            'meta' => $this->parseMeta($config),
            'temp' => $this->parseTemperaturesColors($config),
            'prcp' => $this->parsePrecipitationColors($config),
            'dayt' => $this->parseDaylightColors($config),
            'snow' => $this->parseSnowColors($config),
            'rain' => $this->parseRainColors($config),
            'sqrs' => $this->parseSquareDesign($config),
        ];
    }

    protected function getConfig(string $tbdcScheme = 'default'): ?string
    {
        try {
            $temperatureBlanketDotComUrl = File::get(
                File::dirname(__DIR__).'/../../config/temperature-blanket-dot-com/'.$tbdcScheme.'.txt');
        } catch (FileNotFoundException $e) {
            $temperatureBlanketDotComUrl = null;
        }

        return ! empty($temperatureBlanketDotComUrl) ? $temperatureBlanketDotComUrl : null;
    }

    protected function generateCoordinates(string $key, array $config): ?string
    {
        return (isset($config['meta']['location']['coordinates'][$key])) ?
            $config['meta']['location']['coordinates'][$key] :
            null;
    }

    protected function generateTimezone(array $config): ?string
    {
        return (isset($config['meta']['location']['timezone'])) ?
            $config['meta']['location']['timezone'] :
            null;
    }

    protected function generateDesign(array $config): array
    {
        $meta = $config['sqrs'][0];
        $layout = $config['sqrs'][1];

        $defaultTileKey = array_key_first($meta);

        $tileMap = [
            'tavg' => 'avg',
            'tmin' => 'low',
            'tmax' => 'high',
            'dayt' => 'daylight',
            'snow' => 'precip',
            'rain' => 'precip',
        ];

        $design = array_fill(
            0,
            $meta[$defaultTileKey][0],
            array_fill(
                0,
                $meta[$defaultTileKey][0],
                $tileMap[$defaultTileKey]
            )
        );

        foreach ($layout as $key => $tilesOfKey) {
            foreach ($tilesOfKey as $tileIndex) {
                $row = floor($tileIndex / $meta[$defaultTileKey][0]);
                $column = $tileIndex % $meta[$defaultTileKey][0];
                $design[$row][$column] = $tileMap[$key];
            }
        }

        return $design;
    }

    protected function generateColors(string $key, array $config): array
    {
        $colors = [];

        if (isset($config[$key])) {
            foreach ($config[$key] as $hex => $range) {
                $colorName = Cache::remember('color.'.$hex, $this->cacheTime, function () use ($hex) {
                    return Http::get('https://colornames.org/search/json/?hex='.$hex)['name'] ?? $hex;
                });
                $colors[min($range)] = ['#'.$hex, $colorName];
            }
        }

        ksort($colors, SORT_NUMERIC);

        return $colors;
    }

    protected function parseMeta(array $config): array
    {
        if (! isset($config['l'])) {
            return [];
        }

        $meta = explode("'", $config['l']);
        $geoNameId = rtrim($meta[0], '!');

        return [
            'geoname_id' => $geoNameId,
            'location' => $this->getCoordinatesForGeonameId($geoNameId),
            'date' => rtrim($meta[1], '!'),
        ];
    }

    protected function getCoordinatesForGeonameId($geoNameId): ?array
    {
        return Cache::remember('geoName.'.$geoNameId, $this->cacheTime, function () use ($geoNameId) {
            $url = 'https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/geonames-all-cities-with-a-population-500/records?select=coordinates,timezone&where=geoname_id%3D'.$geoNameId.'&limit=1' ?? null;
            $response = Http::get($url)->body();

            $json = json_decode($response, true);
            if (! isset($json['results'][0])) {
                return null;
            }

            return $json['results'][0];
        });
    }

    protected function parseTemperaturesColors(array $config): array
    {
        if (! isset($config['temp'])) {
            return [];
        }

        return $this->parseRanges(explode('!', $config['temp'])[0]);
    }

    protected function parsePrecipitationColors(array $config): array
    {
        if (! isset($config['prcp'])) {
            return [];
        }

        return $this->parseRanges(explode('!', $config['prcp'])[0]);
    }

    protected function parseDaylightColors(array $config): array
    {
        if (! isset($config['dayt'])) {
            return [];
        }

        return $this->parseRanges(explode('!', $config['dayt'])[0]);
    }

    protected function parseSnowColors(array $config): array
    {
        if (! isset($config['snow'])) {
            return [];
        }

        return $this->parseRanges(explode('!', $config['snow'])[0]);
    }

    protected function parseRainColors(array $config): array
    {
        if (! isset($config['rain'])) {
            return [];
        }

        return $this->parseRanges(explode('!', $config['rain'])[0]);
    }

    protected function parseSquareDesign(array $config): array
    {
        if (! isset($config['sqrs'])) {
            return [];
        }

        $sqrs = explode('!', $config['sqrs']);

        $return = [];
        foreach ($sqrs as $sqr) {
            $return[] = $this->parseRanges($sqr);
        }

        return $return;
    }

    protected function parseRanges(string $config): array
    {
        preg_match_all('/[0-9a-z]*\(.+\)/mU', $config, $ranges);

        $return = [];
        foreach ($ranges[0] as $rangeInfo) {
            $chunks = explode('(', rtrim($rangeInfo, ')'));
            $return[$chunks[0]] = explode("'", $chunks[1]);
        }

        return $return;
    }
}
