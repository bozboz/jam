<?php

/*
|--------------------------------------------------------------------------
| Admin Package Routes
|--------------------------------------------------------------------------
*/

Route::group(array('namespace' => 'Bozboz\Entities\Http\Controllers\Admin', 'prefix' => 'admin'), function() {

	Route::resource('entities', 'EntityController');
	Route::get('entities/{type}/create', 'EntityController@createOfType');

	Route::resource('entity-types', 'EntityTypeController');

	Route::resource('entity-templates', 'EntityTemplateController', ['except' => ['create']]);
	Route::get('entity-templates/{type}/create', 'EntityTemplateController@createForType');

	Route::resource('entity-template-fields', 'EntityTemplateFieldController');
	Route::get('entity-templates-fields/{type}/create', 'EntityTemplateFieldController@createForTemplate');

});
