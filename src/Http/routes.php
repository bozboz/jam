<?php

/*
|--------------------------------------------------------------------------
| Admin Package Routes
|--------------------------------------------------------------------------
*/

Route::group(array('namespace' => 'Bozboz\Entities\Http\Controllers\Admin', 'prefix' => 'admin'), function() {

	Route::resource('entities', 'EntityController', ['except' => ['create']]);
	Route::get('entities/{type}/create', 'EntityController@createOfType');

	Route::resource('entity-types', 'EntityTypeController');

	Route::resource('entity-templates', 'EntityTemplateController', ['except' => ['create']]);
	Route::get('entity-templates/{type}/create', 'EntityTemplateController@createForType');

	Route::resource('entity-template-fields', 'EntityTemplateFieldController', ['except' => ['create']]);
	Route::get('entity-templates-fields/{templateId}/{type}/create', 'EntityTemplateFieldController@createForTemplate');

});
