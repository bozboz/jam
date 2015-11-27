<?php

/*
|--------------------------------------------------------------------------
| Admin Package Routes
|--------------------------------------------------------------------------
*/

Route::group(array('namespace' => 'Bozboz\Entities\Http\Controllers', 'prefix' => 'admin'), function() {

	Route::resource('entity/{type}', 'EntityController');
	Route::get('entity/create/{type}', 'EntityController@createOfType');

	Route::resource('entity-type', 'EntityTypeController');

});
