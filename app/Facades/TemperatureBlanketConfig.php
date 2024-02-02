<?php

namespace App\Facades;

use App\Repositories\TemperatureBlanketRepository;
use Illuminate\Support\Facades\Facade;

class TemperatureBlanketConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TemperatureBlanketRepository::class;
    }
}
