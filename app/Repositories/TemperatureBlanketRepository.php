<?php

namespace App\Repositories;

use App\Facades\TemperatureBlanketDotCom;
use DomainException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

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

        $colorScheme = Config::get($this->prefix.'color');
        $designScheme = Config::get($this->prefix.'design');

        if ($this->temperatureBlanketDotComDesignExists($designScheme)) {
            $temperatureBlanketDotComDesign = TemperatureBlanketDotCom::generate($designScheme);
            $this->setOverride('latitude', $temperatureBlanketDotComDesign['latitude']);
            $this->setOverride('longitude', $temperatureBlanketDotComDesign['longitude']);
            $this->setOverride('timezone', $temperatureBlanketDotComDesign['timezone']);
            $this->setOverride('colors', $temperatureBlanketDotComDesign['colors']);
            $this->setOverride('designs', $temperatureBlanketDotComDesign['design']);
        } else {
            if ($this->colorSchemeExists($colorScheme)) {
                $this->setOverride('colors', $this->colorSchemeContents($colorScheme));
            }
            if ($this->designSchemeExists($designScheme)) {
                $this->setOverride('designs', $this->colorSchemeContents($designScheme));
            }
        }

        $this->overridesSet = true;
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
