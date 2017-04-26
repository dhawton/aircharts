<?php

Route::group(['domain' => 'www.aircharts.org'], function () {
    Route::get('/', 'ACController@getIndex');

    Route::get('/ECR', 'ECRController@getIndex');

    Route::post('/charts', 'ACController@postCharts');
    Route::post('/deploy', function () {
        exec("cd /home/airchar1/ac2/aircharts3 && git pull");
    });

    Route::get('view/{id}', 'ACController@getView');

    Route::get('about', function() { return view('about'); });
    Route::get('about/ecr', function() { return view('ecr'); });
});

Route::group(['middleware' => 'api', 'domain' => 'api.aircharts.org'], function () {
    Route::get('/', function() { echo "Coming soon"; });
    Route::get('/Airport/{data}', 'APIController@getAirport');
    Route::get('v2/Airport/{data}', 'APIv2Controller@getAirport');
});
