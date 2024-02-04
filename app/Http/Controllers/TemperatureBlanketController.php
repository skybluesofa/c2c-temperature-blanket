<?php

namespace App\Http\Controllers;

use App\Facades\OpenMeteo;
use App\Facades\TemperatureBlanketConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
        $yesterday = $this->date->clone()->subDay(1);
        $tomorrow = $this->date->clone()->addDay(1);

        $this->weatherData = collect([
            $yesterday->format('Y-m-d') => OpenMeteo::get($yesterday) ?? null,
            $this->date->format('Y-m-d') => OpenMeteo::get($this->date) ?? null,
            $tomorrow->format('Y-m-d') => OpenMeteo::get($tomorrow) ?? null,
        ]);

        return $this->weatherData;
    }

    protected function getJsonOutput()
    {
        return [
            'meta' => [
                'cachedDate' => OpenMeteo::cachedDate($this->date)?->format('Y-m-d h:i:sa') ?? null,
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
