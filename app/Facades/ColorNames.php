<?php

namespace App\Facades;

use App\Apis\ColorNames\ColorNames as ColorNamesApi;
use Illuminate\Support\Facades\Facade;

class ColorNames extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ColorNamesApi::class;
    }
}
