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

	Route::resource('entity-archive', 'EntityArchiveController', ['only' => ['edit', 'destroy', 'show']]);
	Route::post('entity-archive/{entity}/restore', 'EntityArchiveController@restore');

	Route::group(['prefix' => 'entities/{id}'], function()
	{
		Route::post('publish', [
			'as' => 'admin.entities.publish',
			'uses' => 'EntityController@publish'
		]);

		Route::post('unpublish', [
			'as' => 'admin.entities.unpublish',
			'uses' => 'EntityController@unpublish'
		]);

		Route::post('schedule', [
			'as' => 'admin.entities.schedule',
			'uses' => 'EntityController@schedule'
		]);

		Route::get('duplicate', [
			'as' => 'admin.entities.duplicate',
			'uses' => 'EntityController@duplicate'
		]);
	});

	Route::get('entities/{id}/revisions', 'EntityRevisionController@indexForEntity');
	Route::post('entities/{id}/revisions/revert', 'EntityRevisionController@revert');
	Route::get('entities/revisions/{revision}/diff', 'EntityRevisionController@diff');

	Route::resource('entity-list', 'EntityListController', ['except' => ['create']]);
	Route::get('entity-list/{type}/{template}/{parent_id}/create', [
		'uses' => 'EntityListController@createForEntityListField',
		'as' => 'admin.entity-list.create-for-list'
	]);
	Route::get('entity-list/{type}/{template}/create-for-parent/{parent_id}', 'EntityListController@createOfTypeForParent');
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
