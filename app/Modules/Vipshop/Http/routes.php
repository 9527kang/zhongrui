<?php




Route::group(['prefix' => 'vipshop','middleware' => ['closeSite']], function() {
	Route::get('/','VipshopController@Index')->name('shopIndex');


	Route::get('index','VipshopController@Index')->name('vipIndex');
	Route::get('page','VipshopController@Page')->name('vipPage');
	Route::get('details/{id}','VipshopController@Details')->name('vipDeails');
	Route::get('payvip','VipshopController@getPayvip')->name('payvip');
    Route::post('feedback','VipshopController@feedback')->name('addFeedback');
    Route::get('vipinfo','VipshopController@vipinfo')->name('vipinfo');
});

Route::group(['prefix' => 'vipshop', 'middleware' => ['auth','closeSite']], function (){
    Route::post('payvip','VipshopController@postPayvip');
    Route::get('vipPayorder','VipshopController@vipPayorder')->name('vipPayorder');
    Route::post('vipPayorder', 'VipshopController@postVipPayorder');
    Route::post('thirdPayorder', 'VipshopController@thirdPayorder');
    Route::get('vipsucceed','VipshopController@vipSucceed')->name('vipSucceed');
    Route::get('vipfailure','VipshopController@vipFailure')->name('vipFailure');

});
