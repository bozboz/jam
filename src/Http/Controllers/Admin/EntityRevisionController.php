<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\FormAction;
use Bozboz\Admin\Reports\Actions\LinkAction;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityAtRevisionAction;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\RevisionDecorator;
use Bozboz\Jam\Http\Controllers\Admin\EntityController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class EntityRevisionController extends ModelAdminController
{
	public function __construct(RevisionDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	public function revert($id)
	{
		$modelInstance = $this->decorator->findInstance($id);
		$entity = $modelInstance->entity;
		$entity->currentRevision()->associate($modelInstance);
		$entity->save();

		Revision::whereEntityId($entity->id)->where('created_at', '>', $modelInstance->created_at)->delete();

		$response = $this->getUpdateResponse($modelInstance);
		$response->with('model.updated', sprintf(
			'Successfully reverted "%s"',
			$this->decorator->getLabel($modelInstance)
		));

		return $response;
	}

	protected function getReportActions()
	{
		return [
			new LinkAction(
				['\\'.EntityController::class.'@index', 'type' => Entity::find(Input::get('entity_id'))->template->type->alias],
				[app()->make(EntityController::class), 'canView'],
				[
					'label' => 'Back to listing',
					'icon' => 'fa fa-list-alt',
					'class' => 'btn-default pull-right',
				]
			)
		];
	}

	protected function getRowActions()
	{
		return [
			new EntityAtRevisionAction(
				'\\'.EntityController::class.'@edit',
				[app()->make(EntityController::class), 'canEdit']
			),
			new FormAction(
				$this->getActionName('revert'),
				[app()->make(EntityController::class), 'canEdit'],
				[
					'label' => 'Revert',
					'icon' => 'fa fa-undo',
					'class' => 'btn-warning',
					'method' => 'REVERT',
					'warn' => 'Are you sure you wish to revert to this revision? This action cannot be undone.'
				]
			),
		];
	}

	public function getSuccessResponse($instance)
	{
		return Redirect::action($this->getActionName('index'), ['entity_id' => $instance->entity->id]);
	}
}
