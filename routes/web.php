<?php

Route::get('/', 'ACController@getIndex');
Route::post('/charts','ACController@postCharts');
Route::post('/deploy', function() {
    exec("cd /home/airchar1/ac2/aircharts3 && git pull");
});

Route::group(['middleware' => 'api', 'domain' => 'api.aircharts.org'], function() {
    Route::get('/Airport/{data}', 'APIController@getAirport');
});
