<?php

use App\Http\Controllers\OldTemperatureBlanketController;
use Illuminate\Support\Facades\Route;
use SkyBlueSofa\C2CBlanketFramework\Http\Controllers\TemperatureBlanketController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('c2c-blanket::index', ['info' => app()->make(TemperatureBlanketController::class)->show()]);
});

Route::get('/version', function () {
    return app()->version();
});

Route::get('/generate', [OldTemperatureBlanketController::class, 'generate']);

Route::get('/info', function () {
    return response()->json([
        'stuff' => phpinfo(),
    ]);
});
