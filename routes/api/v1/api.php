<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['namespace' => 'Api\V1\\'], function() {

    Route::group(['prefix' => '/auth'], function () {
        Route::post('/login', 'AuthController@login');
        Route::post('/register', 'AuthController@register');
    });


    Route::group(['middleware' => 'auth:api'], function() {
        Route::get('/users/{user}', 'UserController@show');
    });


});

Route::fallback(function(){
    return response()->json([
        'message' => 'Resource Not Found.',
        'status'  => Response::HTTP_NOT_FOUND,
    ], Response::HTTP_NOT_FOUND);
});