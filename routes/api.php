<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;


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

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@authenticate');

Route::get('test', function() {
    echo 'hello world';
});

Route::group(['middleware' => ['auth:api']], function() {

    Route::prefix('media')->group(function () {
        Route::post('create', 'MediaController@create');
        Route::post('load', 'MediaController@load');
        Route::post('delete', 'MediaController@delete');
    });

    Route::prefix('folder')->group(function () {
        Route::post('rename', 'FolderController@rename');
    });

});
