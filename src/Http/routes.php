<?php

/*
|--------------------------------------------------------------------------
| Admin Package Routes
|--------------------------------------------------------------------------
*/

Route::group(array('middleware' => 'web', 'namespace' => 'Bozboz\Jam\Http\Controllers\Admin', 'prefix' => 'admin'), function() {

	Route::resource('entities', 'EntityController', ['except' => ['index', 'create']]);
	Route::group(['prefix' => 'entities/{type}/{template}'], function()
	{
		Route::get('create', 'EntityController@createOfType');
		Route::get('create-for-parent/{parent_id}', 'EntityController@createOfTypeForParent');
	});
	Route::group(['prefix' => 'entities/{id}'], function()
	{
		Route::post('publish', 'EntityController@publish');
		Route::post('unpublish', 'EntityController@unpublish');
		Route::post('schedule', 'EntityController@schedule');
		Route::get('duplicate', 'EntityController@duplicate');
	});

	Route::get('entities/{id}/revisions', 'EntityRevisionController@indexForEntity');
	Route::post('entities/{id}/revisions/revert', 'EntityRevisionController@revert');
	Route::get('entities/revisions/{revision}/diff', 'EntityRevisionController@diff');

	Route::resource('entity-list', 'EntityListController', ['except' => ['create']]);
	Route::get('entity-list/{type}/{template}/{parent_id}/create', [
		'uses' => 'EntityListController@createForEntityListField',
		'as' => 'admin.entity-list.create-for-list'
	]);
	Route::get('entity-list/{id}/duplicate', 'EntityListController@duplicate');

	Route::resource('entity-types', 'EntityTypeController');

	Route::resource('entity-template-history', 'TemplateHistoryController', ['only' => ['index']]);

	Route::resource('entity-templates', 'EntityTemplateController', ['except' => ['create']]);
	Route::get('entity-templates/{id}/duplicate', 'EntityTemplateController@duplicate');
	Route::post('entity-templates/{id}/duplicate', 'EntityTemplateController@processDuplicate');
	Route::get('entity-templates/{type}/create', 'EntityTemplateController@createForType');

	Route::resource('entity-template-fields', 'EntityTemplateFieldController', ['except' => ['create']]);
	Route::get('entity-template-fields/{templateId}/{type}/create', 'EntityTemplateFieldController@createForTemplate');

});
