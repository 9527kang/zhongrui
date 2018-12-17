<?php





Route::group(['middleware' =>['closeSite']], function () {
    Route::get('/', 'HomeController@index');

    Route::get('/changeCate/{id}', 'HomeController@changeCate');


    Route::get('/sendTaskCode', 'HomeController@sendTaskCode');
    Route::post('/fastPub', 'HomeController@fastPub');
});