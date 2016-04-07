<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Templates\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Redirect;

class EntityListController extends EntityController
{
	public function createForEntityListField($type, $parentEntity)
	{
		$template = Template::with('fields')->whereAlias($type)->first();
		$instance = $this->decorator->newEntityOfType($template);

		if ( ! $this->canCreate($instance)) App::abort(403);

		$parent = $this->decorator->findInstance($parentEntity);

		$foreignKey = $this->foreignKey($template);

		if (!$foreignKey) {
			throw new \LogicException("Attempting to use entity list field with no foreign key set on template.");
		}

		$instance->setAttribute($foreignKey, $parent->id);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	public function store()
	{
		$input = Input::except('after_save');
		$modelInstance = $this->decorator->newModelInstance($input);
		$input[$modelInstance->getKeyName()] = 'NULL';
		$validation = $modelInstance->getValidator();
		$input = $this->decorator->sanitiseInput($input);

		if ($validation->failsStore($input)) {
			return Redirect::back()->withErrors($validation->getErrors())->withInput();
		}

		DB::beginTransaction();

		$this->save($modelInstance, $input);

		$foreignKey = $this->foreignKey($modelInstance->template);
		$parent = $this->decorator->findInstance(Input::get($foreignKey));
		$modelInstance->appendToNode($parent)->save();

		DB::commit();

		$response = $this->reEdit($modelInstance) ?: $this->getStoreResponse($modelInstance);
		$response->with('model.created', sprintf(
			'Successfully created "%s"',
			$this->decorator->getLabel($modelInstance)
		));

		return $response;
	}

	private function foreignKey($template)
	{
		return $template->fields->where('type_alias', 'foreign')->pluck('name')->first();
	}

	protected function getEntityController()
	{
		return EntityController::class;
	}

	/**
	 * The generic response after a successful store/update action.
	 */
	protected function getSuccessResponse($instance)
	{
		$instance->loadAdminValues($instance->latestRevision());
		$foreignKey = $this->foreignKey($instance->template);
		return \Redirect::action('\\' . $this->getEntityController() . '@edit', [$instance->getAttribute($foreignKey)]);
	}

	protected function getListingUrl($instance)
	{
		$instance->loadAdminValues($instance->latestRevision());
		$foreignKey = $this->foreignKey($instance->template);
		return action('\\' . $this->getEntityController() . '@edit', [$instance->getAttribute($foreignKey)]);
	}
}