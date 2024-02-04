<?php

namespace App\Facades;

use App\Apis\GeoNames\GeoNames as GeoNamesApi;
use Illuminate\Support\Facades\Facade;

class GeoNames extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GeoNamesApi::class;
    }
}
