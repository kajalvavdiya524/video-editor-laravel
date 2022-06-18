<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Controllers\Controller;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' =>[EnsureTokenIsValid::class]], function(){

    Route::get('/templates/{id?}', [ApiController::class, 'templates']);
    Route::get('/templates/{id}/fields', [ApiController::class, 'template_fields']);
    Route::get('/templates/{customer_id}/customer', [ApiController::class, 'templates_customer']);
    Route::post('/job',[ApiController::class, 'create_job']);

});
