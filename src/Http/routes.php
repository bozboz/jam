<?php

/*
|--------------------------------------------------------------------------
| Admin Package Routes
|--------------------------------------------------------------------------
*/

Route::group(array('namespace' => 'Bozboz\Entities\Http\Controllers\Admin', 'prefix' => 'admin'), function() {

	Route::resource('entities/{type}', 'EntityController');
	// Route::get('entities/create/{type}', 'EntityController@createOfType');

	// Route::resource('entities/types', 'EntityTypeController');
	// Route::resource('entities/templates', 'EntityTemplatesController');
	// Route::resource('entities/fields', 'EntityFieldsController');

});
