<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Report;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Session;
use Input, Redirect, DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends ModelAdminController
{
	public function __construct(EntityDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	/**
	 * Get an instance of a report to display the model listing
	 *
	 * @return Bozboz\Admin\Reports\Report
	 */
	protected function getListingReport()
	{
		return new Report($this->decorator, 'entities::admin.overview');
	}

	/**
	 * Return an array of params the report requires to render
	 *
	 * @return array
	 */
	protected function getReportParams()
	{
		if (Input::get('type')) {
			$type = Type::where('alias', Input::get('type'))->first();

			if (!$type) {
				throw new NotFoundHttpException;
			}

			$templates = $type->templates()->orderBy('name')->get();
		} else {
			$type = null;
			$templates = Template::orderBy('name')->get();
		}
		return array_merge(parent::getReportParams(), [
			'type' => $type,
			'templates' => $templates,

			'createAction' => $this->getActionName('createOfType'),
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
			$input = $this->decorator->sanitiseInput($input);

			$entity = $this->decorator->newModelInstance($input);

			$validation = $entity->getValidator();

			if ($validation->passesStore($input)) {

				$entity = $this->save($entity, $input);

				$this->newRevision($entity, $input);

				$response = $this->reEdit($entity) ?: $this->getStoreResponse($entity);
			} else {
				$response = Redirect::back()->withErrors($validation->getErrors())->withInput();
			}

			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		Session::flash("model.created", sprintf(
			"Successfully created \"%s\"",
			$this->decorator->getLabel($entity)
		));

		return $response;
	}

	public function edit($id)
	{
		$view = parent::edit($id);

		$values = $view->model->toArray();
		$values += $view->model->latestRevision()->fieldValues()->lists('value', 'key')->toArray();

		$view->with('model', $values);

		return $view;
	}

	public function update($id)
	{
		DB::beginTransaction();
		try {
			$entity = $this->decorator->findInstance($id);
			$validation = $entity->getValidator();
			$input = $this->decorator->sanitiseInput(Input::except('after_save'));
			$input[$entity->getKeyName()] = $entity->getKey();

			if ($validation->passesUpdate($input)) {
				$entity = $this->save($entity, $input);

				$this->newRevision($entity, $input);

				$response = $this->reEdit($entity) ?: $this->getUpdateResponse($entity);
			} else {
				$response = Redirect::back()->withErrors($validation->getErrors())->withInput();
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollback();
			throw $e;
		}

		Session::flash("model.updated", sprintf(
			"Successfully updated \"%s\"",
			$this->decorator->getLabel($entity)
		));

		return $response;
	}

	protected function save($modelInstance, $input)
	{
		$modelInstance->fill($input);
		$modelInstance->save();

		$this->decorator->updateRelations($modelInstance, $input);

		return $modelInstance;
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
