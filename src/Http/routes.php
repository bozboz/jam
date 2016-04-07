<?php

/*
|--------------------------------------------------------------------------
| Admin Package Routes
|--------------------------------------------------------------------------
*/

Route::group(array('middleware' => 'web', 'namespace' => 'Bozboz\Jam\Http\Controllers\Admin', 'prefix' => 'admin'), function() {

	Route::resource('entities', 'EntityController', ['except' => ['create']]);
	Route::get('entities/{type}/create', 'EntityController@createOfType');
	Route::post('entities/{type}/publish', 'EntityController@publish');
	Route::post('entities/{type}/unpublish', 'EntityController@unpublish');
	Route::post('entities/{type}/schedule', 'EntityController@schedule');

	Route::resource('entity-list', 'EntityListController', ['except' => ['create']]);
	Route::get('entity-list/{type}/{parent_id}/create', [
		'uses' => 'EntityListController@createForEntityListField',
		'as' => 'admin.entity-list.create-for-list'
	]);

	Route::get('entities/{id}/revisions', 'EntityRevisionController@indexForEntity');
	Route::post('entities/{id}/revisions/revert', 'EntityRevisionController@revert');

	Route::resource('entity-types', 'EntityTypeController');

	Route::resource('entity-templates', 'EntityTemplateController', ['except' => ['create']]);
	Route::get('entity-templates/{type}/create', 'EntityTemplateController@createForType');

	Route::resource('entity-template-fields', 'EntityTemplateFieldController', ['except' => ['create']]);
	Route::get('entity-templates-fields/{templateId}/{type}/create', 'EntityTemplateFieldController@createForTemplate');

});
