<?php

use App\Http\Controllers\Exchange1C;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::match(['get', 'post'], '/exchange', Exchange1C::class)->withoutMiddleware([VerifyCsrfToken::class]);


Route::get('/', function () {
    return view('welcome');
});
