<?php

namespace App\Http\Controllers;

use App\Facades\OpenMeteo;
use App\Facades\TemperatureBlanketConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TemperatureBlanketController extends Controller
{
    protected ?Carbon $date;

    protected $weatherData;

    public function __construct(Request $request)
    {
        $this->date = new Carbon($request->get('date') ?? null);
    }

    public function show()
    {
        $this->getWeatherData();

        return $this->getJsonOutput();
    }

    protected function getWeatherData(): Collection
    {
        $cachedWeatherData = OpenMeteo::get($this->date);

        $yesterday = $this->date->clone()->subDay(1);
        $tomorrow = $this->date->clone()->addDay(1);
        $displayData = [
            $yesterday->format('Y-m-d') => $cachedWeatherData[$yesterday->format('Y-m-d')] ?? null,
            $this->date->format('Y-m-d') => $cachedWeatherData[$this->date->format('Y-m-d')] ?? null,
            $tomorrow->format('Y-m-d') => $cachedWeatherData[$tomorrow->format('Y-m-d')] ?? null,
        ];

        $this->weatherData = collect($displayData);

        return $this->weatherData;
    }

    protected function getJsonOutput()
    {
        return [
            'meta' => [
                'cachedDate' => Cache::get('openmeteo.'.$this->date->format('Ymd').'.written')?->format('Y-m-d h:i:sa') ?? null,
                'columns' => TemperatureBlanketConfig::get('columns'),
                'design' => TemperatureBlanketConfig::design(),
                'colors' => TemperatureBlanketConfig::colors(),
            ],
            'rows' => [
                'previous' => [
                    'date' => $this->date->clone()->subDays(TemperatureBlanketConfig::get('columns')),
                ],
                'current' => [
                    'date' => $this->date,
                    'cells' => [
                        'previous' => [
                            'date' => $this->date->clone()->subDay(1),
                            'weather' => $this->weatherData->first() ?? null,
                            'show' => true,
                        ],
                        'current' => [
                            'date' => $this->date,
                            'weather' => $this->weatherData->nth(2, 1)->first() ?? null,
                            'show' => true,
                        ],
                        'next' => [
                            'date' => $this->date->clone()->addDay(1),
                            'weather' => $this->weatherData->last() ?? null,
                            'show' => is_array($this->weatherData->last() ?? null),
                        ],
                    ],
                ],
                'next' => [
                    'date' => $this->date->clone()->addDays(TemperatureBlanketConfig::get('columns')),
                ],
            ],
        ];
    }
}
