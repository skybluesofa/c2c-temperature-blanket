<?php

namespace App\Facades;

use App\Apis\OpenMeteo\OpenMeteo as OpenMeteoApi;
use Illuminate\Support\Facades\Facade;

class OpenMeteo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return OpenMeteoApi::class;
    }
}
