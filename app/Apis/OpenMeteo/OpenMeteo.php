<?php

namespace App\Apis\OpenMeteo;

use App\Facades\TemperatureBlanketConfig;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenMeteo
{
    protected ?Carbon $date;

    protected string $latitude;

    protected string $longitude;

    protected string $timezone;

    public function __construct(?Carbon $date)
    {
        $this->date = $date ?? new Carbon();
        $this->latitude = TemperatureBlanketConfig::get('latitude');
        $this->longitude = TemperatureBlanketConfig::get('longitude');
        $this->timezone = TemperatureBlanketConfig::get('timezone');
    }

    public function get(): Collection
    {
        return collect(Cache::remember('openmeteo.'.$this->date->format('Ymd'), 60 * 60, function () {
            $now = Carbon::now();
            $today = new Carbon($this->date);
            $startDate = $today->clone()->subMonth()->format('Y-m-01');
            $endDate = $today->clone()->addMonth();
            $endDate = ($endDate > $now) ? $now->format('Y-m-d') : $endDate->format('Y-m-01');
            $baseUrl = 'https://archive-api.open-meteo.com/v1/archive?latitude='.$this->latitude.'&longitude='.$this->longitude.'&start_date='.$startDate.'&end_date='.$endDate.'&daily=temperature_2m_max,temperature_2m_min,temperature_2m_mean,daylight_duration,rain_sum,snowfall_sum&temperature_unit=fahrenheit&precipitation_unit=inch&timezone='.$this->timezone;
            $response = Http::get($baseUrl);

            $data = [];
            $weatherInfo = json_decode($response, true);

            foreach ($weatherInfo['daily']['time'] as $key => $date) {
                $data[$date] = [
                    'date' => $date,
                    'temp' => [
                        'high' => null,
                        'low' => null,
                        'avg' => null,
                    ],
                    'daylight' => number_format(($weatherInfo['daily']['daylight_duration'][$key] / 3600), 2),
                    'precipitation' => [
                        'rain' => null,
                        'snow' => null,
                    ],
                ];

                if ((new Carbon($date))->format('Ymd') <= (new Carbon())->format('Ymd')) {
                    $data[$date]['precipitation']['rain'] = number_format($weatherInfo['daily']['rain_sum'][$key], 2);
                    $data[$date]['precipitation']['snow'] = number_format($weatherInfo['daily']['snowfall_sum'][$key], 2);
                    $data[$date]['temp']['high'] = $weatherInfo['daily']['temperature_2m_max'][$key];
                    $data[$date]['temp']['low'] = $weatherInfo['daily']['temperature_2m_min'][$key];

                    $average = $weatherInfo['daily']['temperature_2m_mean'][$key];
                    if (empty($average) && ! is_null($weatherInfo['daily']['temperature_2m_max'][$key]) && ! is_null($weatherInfo['daily']['temperature_2m_min'][$key])) {
                        $average = ($weatherInfo['daily']['temperature_2m_max'][$key] + $weatherInfo['daily']['temperature_2m_min'][$key]) / 2;
                    }
                    $data[$date]['temp']['avg'] = $average;
                }
            }

            Cache::set('openmeteo.'.$today->format('Ymd').'.written', Carbon::now('America/Chicago'));

            return $data;
        }));
    }
}
