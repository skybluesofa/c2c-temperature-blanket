<?php

namespace App\Repositories;

use App\Facades\TemperatureBlanketDotCom;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;

class TemperatureBlanketRepository
{
    protected string $prefix = 'c2c.';

    protected bool $overridesSet = false;

    public function get($key, $default = null): mixed
    {
        $this->setOverrides();

        return Config::get($this->prefix.$key, $default);
    }

    protected function setOverrides(): void
    {
        if ($this->overridesSet) {
            return;
        }

        //        if (Config::get($this->prefix.'url_config', false)) {
        //        } else {
        $this->setFilesystemOverrides();
        $this->setDefaultOverrides();
        $this->setUrlOverrides();
        //        }

        $this->overridesSet = true;
    }

    protected function setFilesystemOverrides()
    {
        $colorScheme = Config::get($this->prefix.'color');
        $designScheme = Config::get($this->prefix.'design');

        if ($this->temperatureBlanketDotComDesignExists($designScheme)) {
            $temperatureBlanketDotComDesign = TemperatureBlanketDotCom::generate($designScheme);
            $this->setOverride('latitude', $temperatureBlanketDotComDesign['latitude']);
            $this->setOverride('longitude', $temperatureBlanketDotComDesign['longitude']);
            $this->setOverride('timezone', $temperatureBlanketDotComDesign['timezone']);
            $this->setOverride('colors.loaded', $temperatureBlanketDotComDesign['colors']);
            $this->setOverride('designs.loaded', $temperatureBlanketDotComDesign['design']);
        } else {
            if ($this->colorSchemeExists($colorScheme)) {
                $this->setOverride('colors.loaded', $this->colorSchemeContents($colorScheme));
            }
            if ($this->designSchemeExists($designScheme)) {
                $this->setOverride('designs.loaded', $this->colorSchemeContents($designScheme));
            }
        }
    }

    protected function setDefaultOverrides()
    {
        $this->setOverride('year', (new Carbon)->format('Y'));
    }

    protected function setUrlOverrides()
    {
        if ($year = Request::get('year')) {
            $this->setOverride('year', $year);
        }
        if ($latitude = Request::get('lat')) {
            $this->setOverride('latitude', $latitude);
        }
        if ($longitude = Request::get('lon')) {
            $this->setOverride('longitude', $longitude);
        }
        if ($timezone = Request::get('tz')) {
            $this->setOverride('timezone', $timezone);
        }
        $this->parseAndSetUrlTemperatures();
        $this->parseAndSetUrlDaylightHours();
        $this->parseAndSetUrlPrecipitation();
        $this->parseAndSetUrlDesign();
    }

    protected function parseAndSetUrlTemperatures(): void
    {
        $parsedTemperatures = $this->parseUrlRange('temp');

        if (! empty($parsedTemperatures)) {
            $this->setOverride('colors.loaded.temperature', $parsedTemperatures);
        }
    }

    protected function parseAndSetUrlDaylightHours(): void
    {
        $parsedHours = $this->parseUrlRange('day');

        if (! empty($parsedHours)) {
            $this->setOverride('colors.loaded.daylight', $parsedHours);
        }
    }

    protected function parseAndSetUrlPrecipitation(): void
    {
        foreach (['rain', 'snow'] as $precipitationType) {
            $parsedPrecipitation = $this->parseUrlRange($precipitationType);

            if (! empty($parsedPrecipitation)) {
                $this->setOverride('colors.loaded.precipitation.'.$precipitationType, $parsedPrecipitation);
            }
        }
    }

    protected function parseUrlRange($key): array
    {
        if (! $urlRange = Request::get($key)) {
            return [];
        }

        $parsedRange = [];

        $rangeConfigs = explode('|', $urlRange);
        foreach ($rangeConfigs as $rangeConfig) {
            $rangeConfig = explode(',', $rangeConfig);
            if (is_array($rangeConfig) && count($rangeConfig) >= 2) {
                $parsedRange[$rangeConfig[0]] = ['#'.$rangeConfig[1], $rangeConfig[1]];
                if (isset($rangeConfig[2])) {
                    $parsedRange[$rangeConfig[0]][1] = $rangeConfig[2];
                }
            }
        }

        return $parsedRange;
    }

    protected function parseAndSetUrlDesign(): array
    {
        return [];
        if (! $urlRange = Request::get('design')) {
            return [];
        }
    }

    protected function setOverride(string $key, mixed $value)
    {
        Config::set($this->prefix.$key, $value);
    }

    public function design(): array
    {
        $designName = Config::get('c2c.design');

        // First, try to load a Temperature-Blanket.com URL
        if ($designScheme = self::temperatureBlanketDotComDesignContents($designName)) {
            return $designScheme['design'];
        }

        // Next, try to load a saved design file
        elseif (empty($designScheme = Config::get('c2c.designs.'.$designName))) {
            if (! File::exists(File::dirname(__DIR__).'/../../../config/designs/'.$designName.'.txt')) {
                throw new FileNotFoundException("Design Scheme '".$designName.".txt' cannot be located in 'config/designs/' folder");
            }
        }

        // Finally, try to load a standard design from the base config
        else {
            $designScheme = Config::get('c2c.designs.'.$designName);
        }

        if (empty($designScheme)) {
            throw new DomainException("Design Scheme '".$designName."' cannot be located in 'config/c2c.php' file");
        }

        return $designScheme;
    }

    public function colors(): array
    {
        if (! empty($urlColors = Config::get('c2c.colors.loaded'))) {
            return $urlColors;
        }

        $designName = Config::get('c2c.color');

        if ($designScheme = self::temperatureBlanketDotComDesignContents($designName)) {
            return $designScheme['colors'];
        } elseif (empty($designScheme = Config::get('c2c.colors.'.$designName))) {
            if (! File::exists(File::dirname(__DIR__).'/../../../config/colors/'.$designName.'.txt')) {
                throw new FileNotFoundException("Color Scheme '".$designName.".txt' cannot be located in 'config/colors/' folder");
            }
        } else {
            $designScheme = Config::get('c2c.colors.'.$designName);
        }

        if (empty($designScheme)) {
            throw new DomainException("Color Scheme '".$designName."' cannot be located in 'config/c2c.php' file");
        }

        return $designScheme;
    }

    protected function temperatureBlanketDotComDesignExists(string $designName): bool
    {
        return File::exists(App::configPath().'/temperature-blanket-dot-com/'.$designName.'.txt');
    }

    public function temperatureBlanketDotComDesignContents(string $designName): ?array
    {
        if (! $this->temperatureBlanketDotComDesignExists($designName)) {
            return null;
        }

        return TemperatureBlanketDotCom::generate($designName);
    }

    protected function colorSchemeExists(string $schemeName): bool
    {
        return File::exists(App::configPath().'/colors/'.$schemeName.'.txt');
    }

    protected function colorSchemeContents(string $schemeName): ?array
    {
        if (! $this->colorSchemeExists($schemeName)) {
            return null;
        }

        return File::get(App::configPath().'/colors/'.$schemeName.'.txt');
    }

    protected function designSchemeExists(string $schemeName): bool
    {
        return File::exists(App::configPath().'/designs/'.$schemeName.'.txt');
    }

    protected function designSchemeContents(string $schemeName): ?array
    {
        if (! $this->designSchemeExists($schemeName)) {
            return null;
        }

        return File::get(App::configPath().'/designs/'.$schemeName.'.txt');
    }
}
