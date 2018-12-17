<?php




Route::group(['prefix' => 'substation', 'middleware' => ['substation','closeSite']], function() {
	Route::get('/tasks/{id}','TaskController@getTasks')->name('substation_tasks');
	Route::get('/service/{id}','ServiceController@getService')->name('substation_service');
	Route::get('/{id}','TaskController@index')->name('substation/index');
});
