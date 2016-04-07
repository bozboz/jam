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
			throw new \LogicException("Attempting to use entity list field with no entity-list-foreign field in template.");
		}

		$instance->setAttribute($foreignKey, $parent->id);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	/**
	 * Ensure that the entity has a foreign key field
	 */
	private function foreignKey($template)
	{
		return $template->fields->where('name', 'list_parent')->pluck('name')->first();
	}

	protected function getEntityController()
	{
		return EntityController::class;
	}

	private function getParentId($instance)
	{
		$revision = $instance->latestRevision();
		if ($revision) {
			$instance->loadAdminValues($revision);
			$foreignKey = $this->foreignKey($instance->template);
			return $instance->getAttribute($foreignKey);
		}
	}

	/**
	 * The generic response after a successful store/update action.
	 */
	protected function getSuccessResponse($instance)
	{
		return \Redirect::action('\\' . $this->getEntityController() . '@edit', [$this->getParentId($instance)]);
	}

	protected function getListingUrl($instance)
	{
		return action('\\' . $this->getEntityController() . '@edit', [$this->getParentId($instance)]);
	}
}