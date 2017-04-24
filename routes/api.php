<?php

use Illuminate\Http\Request;

Route::group(['middleware' => 'api', 'domain' => 'api.aircharts.org'], function() {
    Route::get('Airport/{data}', 'APIController@getAirport');
});