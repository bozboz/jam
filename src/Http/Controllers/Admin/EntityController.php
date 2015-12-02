<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Exceptions\PageValidationException;
use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Report;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Templates\Template;
use Input, Redirect, DB;

class EntityController extends ModelAdminController
{
	public function __construct(EntityDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	public function index()
	{
		$report = new Report($this->decorator);
		// $report->overrideView('sitemap.admin.overview');

		return $report->render([
			'controller' => get_class($this),
			'pageTypes' => Template::all()
		]);
	}

	public function createOfType($type)
	{
		$template = Template::whereAlias($type)->first();

		$entity = $this->decorator->newEntityOfType($template);

		return $this->renderCreateFormFor($entity);
	}

	public function store()
	{
		DB::beginTransaction();
		try {
			$input = Input::except('after_save');
			$entity = $this->decorator->newModelInstance($input);

			$input = $this->validate($entity, $input);
			$entity = $this->save($entity, $input);

			$this->newRevision($entity, $input);

			$response = $this->reEdit($entity) ?: $this->getStoreResponse($entity);
			DB::commit();
		} catch (PageValidationException $e) {
			$response = Redirect::back()->withErrors($e->getErrors())->withInput();
			DB::rollback();
		}

		return $response;
	}

	public function edit($id)
	{
		$view = parent::edit($id);

		$values = $view->model->toArray();
		$values += $view->model->latestRevision()->fieldValues()->lists('value', 'key');

		$view->with('model', $values);

		return $view;
	}

	public function update($id)
	{
		DB::beginTransaction();
		try {
			$input = Input::except('after_save');
			$entity = $this->decorator->findInstance($id);

			$input = $this->validate($entity, $input);
			$entity = $this->save($entity, $input);

			$this->newRevision($entity, $input);

			$response = $this->reEdit($entity) ?: $this->getUpdateResponse($entity);
			DB::commit();
		} catch (PageValidationException $e) {
			$response = Redirect::back()->withErrors($e->getErrors())->withInput();
			DB::rollback();
		}

		return $response;
	}

	protected function newRevision($entity, $input)
	{
		$latestRevision = $entity->latestRevision();

		if ($latestRevision) {
			$entity->loadValues($latestRevision);
			$currentValues = $entity->getValue();
			$changes = array_diff_assoc($currentValues, $input);
		}

		if ( ! $latestRevision || $changes) {
			$entity->newRevision($input);
		}
	}
}
