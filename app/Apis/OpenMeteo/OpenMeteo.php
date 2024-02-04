<?php

namespace App\Apis\OpenMeteo;

use App\Facades\TemperatureBlanketConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenMeteo
{
    protected ?Carbon $date;

    protected string $latitude;

    protected string $longitude;

    protected string $timezone;

    protected int $ttl = 60 * 60 * 24;

    public function __construct(?Carbon $date)
    {
        $this->date = $date ?? new Carbon();
        $this->latitude = TemperatureBlanketConfig::get('latitude');
        $this->longitude = TemperatureBlanketConfig::get('longitude');
        $this->timezone = TemperatureBlanketConfig::get('timezone');
    }

    public function get(?Carbon $date = null): ?array
    {
        $this->date = $date ?? new Carbon();

        if (! Cache::has($this->getCacheKey($this->date))) {
            $now = Carbon::now();
            $today = new Carbon($this->date);
            $startDate = $today->clone()->subMonth()->format('Y-m-01');
            $endDate = $today->clone()->addMonth();
            $endDate = ($endDate > $now) ? $now->format('Y-m-d') : $endDate->format('Y-m-01');
            $baseUrl = 'https://archive-api.open-meteo.com/v1/archive?latitude='.$this->latitude.'&longitude='.$this->longitude.'&start_date='.$startDate.'&end_date='.$endDate.'&daily=temperature_2m_max,temperature_2m_min,temperature_2m_mean,daylight_duration,rain_sum,snowfall_sum&temperature_unit=fahrenheit&precipitation_unit=inch&timezone='.$this->timezone;

            $weatherInfo = json_decode(Http::get($baseUrl), true);
            foreach ($weatherInfo['daily']['time'] as $key => $date) {
                $weatherDate = new Carbon($date);
                if ($weatherDate->format('Ymd') > $now->format('Ymd')) {
                    continue;
                }

                $average = $weatherInfo['daily']['temperature_2m_mean'][$key];
                if (empty($average) && ! is_null($weatherInfo['daily']['temperature_2m_max'][$key]) && ! is_null($weatherInfo['daily']['temperature_2m_min'][$key])) {
                    $average = ($weatherInfo['daily']['temperature_2m_max'][$key] + $weatherInfo['daily']['temperature_2m_min'][$key]) / 2;
                }

                $data = null;
                if (! is_null($average)) {
                    $data = [
                        'date' => $date,
                        'temp' => [
                            'high' => $weatherInfo['daily']['temperature_2m_max'][$key],
                            'low' => $weatherInfo['daily']['temperature_2m_min'][$key],
                            'avg' => $average,
                        ],
                        'daylight' => number_format(($weatherInfo['daily']['daylight_duration'][$key] / 3600), 2),
                        'precipitation' => [
                            'rain' => number_format($weatherInfo['daily']['rain_sum'][$key], 2),
                            'snow' => number_format($weatherInfo['daily']['snowfall_sum'][$key], 2),
                        ],
                    ];
                }

                Cache::set($this->getCacheKey($weatherDate), $data, $this->ttl);
                Cache::set($this->getCacheWrittenKey($weatherDate), $now, $this->ttl);
            }
        }

        return Cache::get($this->getCacheKey($this->date));
    }

    public function cachedDate(Carbon $date): Carbon
    {
        return Cache::get($this->getCacheWrittenKey($date));
    }

    protected function getCacheKey(Carbon $date): string
    {
        return 'openmeteo.'.$this->latitude.'.'.$this->longitude.'.'.$date->format('Ymd');
    }

    protected function getCacheWrittenKey(Carbon $date): string
    {
        return $this->getCacheKey($date).'.written';
    }
}
