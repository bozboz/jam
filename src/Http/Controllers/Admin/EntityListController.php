<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Templates\Template;
use Redirect;

class EntityListController extends EntityController
{
	public function createForEntityListField($type, $parentEntity)
	{
		$template = Template::with('fields')->whereAlias($type)->first();
		$instance = $this->decorator->newEntityOfType($template);

		if ( ! $this->canCreate($instance)) App::abort(403);

		$parent = Entity::find($parentEntity);

		$foreignKey = $template->fields->where('type_alias', 'foreign')->pluck('name')->first();

		if (!$foreignKey) {
			throw new \LogicException("Attempting to use entity list field with no foreign key set on template.");
		}

		$instance->setAttribute($foreignKey, $parent->id);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	/**
	 * The generic response after a successful store/update action.
	 */
	protected function getSuccessResponse($instance)
	{
		$instance->loadRealValues();
		$foreignKey = $instance->template->fields->where('type_alias', 'foreign')->pluck('name')->first();
		return \Redirect::action('\\' . EntityController::class . '@edit', [$instance->getAttribute($foreignKey)]);
	}

	protected function getListingUrl($instance)
	{
		$instance->loadRealValues();
		$foreignKey = $instance->template->fields->where('type_alias', 'foreign')->pluck('name')->first();
		return action('\\' . EntityController::class . '@edit', [$instance->getAttribute($foreignKey)]);
	}
}