<?php




Route::group(['prefix' => 'im'], function() {

});




Route::group(['prefix' => 'im'], function () {
	
    Route::get('message/{uid}', 'IndexController@getMessage');
    Route::post('addAttention', 'IndexController@addAttention');


    
    Route::post('imUserList', 'IndexController@imUserList');

    Route::get('imBlade', 'IndexController@imBlade');

    Route::post('getImUserInfo', 'IndexController@getImUserInfo');
});
