<?php

namespace App\Http\Controllers;

use App\Facades\OpenMeteo;
use App\Facades\TemperatureBlanketDotCom;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class OldTemperatureBlanketController extends Controller
{
    protected ?Carbon $date;

    protected $weatherData;

    public function __construct(Request $request)
    {
        $this->date = new Carbon($request->get('date') ?? null);
        TemperatureBlanketDotCom::generate();
    }

    public function generate(Request $request)
    {
        $this->date = new Carbon($request->get('date') ?? null);

        $this->getWeatherData();
        $this->getHtmlOutput();
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

    public function getJsonOutput()
    {
        $info = [
            'meta' => [
                'cachedDate' => Cache::get('openmeteo.'.$this->date->format('Ymd').'.written')?->format('Y-m-d h:i:sa') ?? null,
                'columns' => Config::get('c2c.columns'),
                'design' => $this->loadDesign(),
                'colors' => $this->loadColors(),
            ],
            'rows' => [
                'previous' => [
                    'date' => $this->date->clone()->subDays(Config::get('c2c.columns')),
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
                    'date' => $this->date->clone()->addDays(Config::get('c2c.columns')),
                ],
            ],
        ];

        /*
        print "<pre>";
        print_r($info);
        die();
        */
        return $info;
    }

    protected function loadDesign(): array
    {
        $designName = Config::get('c2c.design');
        $designScheme = Config::get('c2c.designs.'.$designName);

        if (empty($designScheme)) {
            if (! File::exists(File::dirname(__DIR__).'/../../../config/designs/'.$designName.'.txt')) {
                throw new FileNotFoundException("Design Scheme '".$designName.".txt' cannot be located in 'config/designs/' folder");
            }

            $designScheme = Config::get('designs.'.$designName);
        }

        return $designScheme;
    }

    protected function loadColors(): array
    {
        $designName = Config::get('c2c.color');
        $designScheme = Config::get('c2c.colors.'.$designName);

        if (empty($designScheme)) {
            if (! File::exists(File::dirname(__DIR__).'/../../../config/colors/'.$designName.'.txt')) {
                throw new FileNotFoundException("Color Scheme '".$designName.".txt' cannot be located in 'config/colors/' folder");
            }

            $designScheme = Config::get('colors.'.$designName);
        }

        return $designScheme;
    }

    protected function getHtmlOutput()
    {
        $prevDate = $this->date->clone()->subDay(1);
        $prevRowDate = $this->date->clone()->subDays(Config::get('c2c.columns'));
        $nextDate = $this->date->clone()->addDay(1);
        $nextRowDate = $this->date->clone()->addDays(Config::get('c2c.columns'));

        $cachedDate = Cache::get('openmeteo.'.$this->date->format('Ymd').'.written')?->format('Y-m-d h:i:sa') ?? 'n/a';

        $first = $this->weatherData->first() ?? null;
        $second = $this->weatherData->nth(2, 1)->first() ?? null;
        $third = $this->weatherData->last() ?? null;
        $showThird = is_array($third);

        echo '<html><body style="background:#000;">';
        echo '<h1 style="text-align:center;color:#fff">2024 Temperature Blanket</h1>';
        echo '<div style="color:#fff;position:absolute;top:10px;right:10px;">Data from '.$cachedDate.'</div>';
        echo '<h2 style="text-align:center;color:#fff"><a href="/?date='.$prevDate->format('m/d/Y').'" style="text-decoration:none;color:#fff;">&lt;</a> &nbsp;';
        echo $this->date->format('m/d/Y');
        echo '&nbsp; <a href="/?date='.$nextDate->format('m/d/Y').'" style="text-decoration:none;color:#fff">&gt;</a></h2>';

        echo '<table border=1 style="width:100%;">';
        echo '<tr>';
        echo $this->generateMetaCell($prevDate);
        echo $this->generateMetaCell($this->date);
        if ($showThird) {
            echo $this->generateMetaCell($nextDate);
        }
        echo '</tr>';
        echo '<tr>';
        echo $this->generatePrecipCell($first);
        echo $this->generateTempCell($first, 'high');
        echo $this->generateTempCell($first, 'high');
        echo $this->generateTempCell($first, 'avg');

        echo $this->generatePrecipCell($second);
        echo $this->generateTempCell($second, 'high');
        echo $this->generateTempCell($second, 'high');
        echo $this->generateTempCell($second, 'avg');

        if ($showThird) {
            echo $this->generatePrecipCell($third);
            echo $this->generateTempCell($third, 'high');
            echo $this->generateTempCell($third, 'high');
            echo $this->generateTempCell($third, 'avg');
        }
        echo '</tr>';
        echo '<tr>';
        echo $this->generateTempCell($first, 'high');
        echo $this->generateTempCell($first, 'high');
        echo $this->generateTempCell($first, 'avg');
        echo $this->generateTempCell($first, 'low');

        echo $this->generateTempCell($second, 'high');
        echo $this->generateTempCell($second, 'high');
        echo $this->generateTempCell($second, 'avg');
        echo $this->generateTempCell($second, 'low');

        if ($showThird) {
            echo $this->generateTempCell($third, 'high');
            echo $this->generateTempCell($third, 'high');
            echo $this->generateTempCell($third, 'avg');
            echo $this->generateTempCell($third, 'low');
        }
        echo '</tr>';
        echo '<tr>';
        echo $this->generateTempCell($first, 'high');
        echo $this->generateTempCell($first, 'avg');
        echo $this->generateTempCell($first, 'low');
        echo $this->generateTempCell($first, 'low');

        echo $this->generateTempCell($second, 'high');
        echo $this->generateTempCell($second, 'avg');
        echo $this->generateTempCell($second, 'low');
        echo $this->generateTempCell($second, 'low');

        if ($showThird) {
            echo $this->generateTempCell($third, 'high');
            echo $this->generateTempCell($third, 'avg');
            echo $this->generateTempCell($third, 'low');
            echo $this->generateTempCell($third, 'low');
        }
        echo '</tr>';
        echo '<tr>';
        echo $this->generateTempCell($first, 'avg');
        echo $this->generateTempCell($first, 'low');
        echo $this->generateTempCell($first, 'low');
        echo $this->generateDaylightCell($first);

        echo $this->generateTempCell($second, 'avg');
        echo $this->generateTempCell($second, 'low');
        echo $this->generateTempCell($second, 'low');
        echo $this->generateDaylightCell($second);

        if ($showThird) {
            echo $this->generateTempCell($third, 'avg');
            echo $this->generateTempCell($third, 'low');
            echo $this->generateTempCell($third, 'low');
            echo $this->generateDaylightCell($third);
        }
        echo '</tr>';

        echo '</table>';
        echo '<script>document.onkeydown = checkKey;

        function checkKey(e) {
            e = e || window.event;
            if (e.keyCode == "38") {
                document.location.href="/generate?date='.$prevRowDate->format('m/d/Y').'";
            }';
        if ($showThird) {

            echo 'else if (e.keyCode == "40") {
                document.location.href="/generate?date='.$nextRowDate->format('m/d/Y').'";
            }';
        }
        echo 'else if (e.keyCode == "37") {
                document.location.href="/generate?date='.$prevDate->format('m/d/Y').'";
            }';
        if ($showThird) {
            echo 'else if (e.keyCode == "39") {
                document.location.href="/generate?date='.$nextDate->format('m/d/Y').'";
            }';
        }

        echo '}</script>';
        echo '</body></html>';
    }

    protected function generateMetaCell(Carbon $date): string
    {
        $first = new Carbon('2023-12-31');

        $totalColumns = 16;
        $column = 1;
        $row = 1;

        $diff = $first->diffInDays($date);

        $column = ($diff % $totalColumns) + 1;
        $row = floor($diff / $totalColumns) + 1;

        return '<td colspan=4 style="background:#000;height:100px;color:#fff;font-family:arial;font-size:16px;">'.$date->format('m/d/Y').'<br>Row: '.$row.'; Column: '.$column.'</td>';
    }

    protected function generatePrecipCell($info): string
    {
        $rain = 0;
        $snowfall = 0;

        if (is_null($info['precipitation']['snow']) && is_null($info['precipitation']['rain'])) {
            $hex = '#000';
            $color = 'No Data';
            $precipitation = '0';
            $precipitationType = '';
        } else {
            $snowfall = $info['precipitation']['snow'];
            $rain = $info['precipitation']['rain'];
            $averageTemp = $info['temp']['avg'];

            if ($snowfall == $rain) {
                // We'll figure out which color pattern to use
                // based on the temperature
                $hex = '#c18351';
                $color = 'Latte';
                $precipitation = $snowfall;
                $precipitationType = 'snow';
                if ($averageTemp >= 32) {
                    $hex = '#4f4839';
                    $color = 'Clover';
                    $precipitationType = 'rain';
                }
            } elseif ($snowfall >= $rain) {
                $hex = '#c18351';
                $color = 'Latte';
                $precipitation = $snowfall;
                $precipitationType = 'snow';
                if ($snowfall > 0) {
                    $hex = '#e29d2a';
                    $color = 'Sungold';
                }
                if ($snowfall > 2) {
                    $hex = '#c54712';
                    $color = 'Burnt Pumpkin';
                }
                if ($snowfall > 6) {
                    $hex = '#87170e';
                    $color = 'Terra Cotta';
                }
            } else {
                $hex = '#4f4839';
                $color = 'Clover';
                $precipitation = $rain;
                $precipitationType = 'rain';
                if ($rain > 0) {
                    $hex = '#404622';
                    $color = 'Dark Olive';
                }
                if ($rain > 2) {
                    $hex = '#445259';
                    $color = 'Blue Spruce';
                }
                if ($rain > 6) {
                    $hex = '#0a4552';
                    $color = 'Antique Teal';
                }
            }
        }

        return '<td style="background:'.$hex.';width:100px;height:100px;color:#fff;font-family:arial;font-size:16px;">'.$color.'<br>'.$precipitation.'" '.$precipitationType.'</td>';
    }

    protected function generateDaylightCell($info): string
    {
        $daylight = $info['daylight'];

        if (is_null($daylight)) {
            $hex = '#000';
            $color = 'No Data';
        } else {
            $daylight = intval($daylight * 100) / 100;
            $hex = '#65371b';
            $color = 'Cinnamon';
            if ($daylight > 10) {
                $hex = '#87170e';
                $color = 'Terra Cotta';
            }
            if ($daylight > 11) {
                $hex = '#c54712';
                $color = 'Burnt Pumpkini';
            }
            if ($daylight > 12) {
                $hex = '#e29d2a';
                $color = 'Sungold';
            }
            if ($daylight > 13) {
                $hex = '#c18351';
                $color = 'Latte';
            }
            if ($daylight > 14) {
                $hex = '#404622';
                $color = 'Dark Olive';
            }
            if ($daylight > 15) {
                $hex = '#0a4552';
                $color = 'Antique Teal';
            }
        }

        return '<td style="background:'.$hex.';width:100px;height:100px;color:#fff;font-family:arial;font-size:16px;">'.$color.'<br>'.$daylight.' hours</td>';
    }

    protected function generateTempCell($info, $state): string
    {
        $temp = $info['temp'][$state];

        if (is_null($info['temp']['avg'])) {
            $hex = '#000';
            $color = 'No Data';
        } else {
            $hex = '#473438';
            $color = 'Graphite';
            if ($temp > 0) {
                $hex = '#202b46';
                $color = 'Dark Denim';
            }
            if ($temp > 10) {
                $hex = '#0a4552';
                $color = 'Antique Teal';
            }
            if ($temp > 20) {
                $hex = '#445259';
                $color = 'Blue Spruce';
            }
            if ($temp > 30) {
                $hex = '#404622';
                $color = 'Dark Olive';
            }
            if ($temp > 40) {
                $hex = '#4f4839';
                $color = 'Clover';
            }
            if ($temp > 50) {
                $hex = '#c18351';
                $color = 'Latte';
            }
            if ($temp > 60) {
                $hex = '#e29d2a';
                $color = 'Sungold';
            }
            if ($temp > 70) {
                $hex = '#c54712';
                $color = 'Burnt Pumpkin';
            }
            if ($temp > 80) {
                $hex = '#87170e';
                $color = 'Terra Cotta';
            }
            if ($temp > 90) {
                $hex = '#65371b';
                $color = 'Cinnamon';
            }
            if ($temp > 100) {
                $hex = '#3e261f';
                $color = 'Coffee';
            }
        }

        return '<td style="background:'.$hex.';width:100px;height:100px;color:#fff;font-family:arial;font-size:16px;">'.$color.'<br>'.$temp.'&deg;</td>';
    }
}
