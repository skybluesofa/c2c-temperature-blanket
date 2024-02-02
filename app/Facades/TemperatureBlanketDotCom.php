<?php

namespace App\Facades;

use App\Apis\TemperatureBlanketDotCom\TemperatureBlanketDotCom as TemperatureBlanketDotComApi;
use Illuminate\Support\Facades\Facade;

class TemperatureBlanketDotCom extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TemperatureBlanketDotComApi::class;
    }
}
