<?php

use Illuminate\Support\Facades\Route;

Route::get(
    '/test/kucoin',
    [
        \App\Http\Controllers\ControllerTestExchange::class,
        'testKucoin'
    ]
);
