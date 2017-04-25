<?php

Route::group(['domain' => 'www.aircharts.org'], function () {
    Route::get('/', 'ACController@getIndex');

    Route::get('/ECR', 'ECRController@getIndex');

    Route::post('/charts', 'ACController@postCharts');
    Route::post('/deploy', function () {
        exec("cd /home/airchar1/ac2/aircharts3 && git pull");
    });

    Route::get('view/{id}', 'ACController@getView');
});

Route::group(['middleware' => 'api', 'domain' => 'api.aircharts.org'], function () {
    Route::get('/', function() { echo "Coming soon"; });
    Route::get('/Airport/{data}', 'APIController@getAirport');
});
