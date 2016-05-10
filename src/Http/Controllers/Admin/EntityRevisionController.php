<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Custom as CustomUrl;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\RevisionDecorator;
use Bozboz\Jam\Entities\RevisionReport;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class EntityRevisionController extends ModelAdminController
{
	protected $useActions = true;

	public function __construct(RevisionDecorator $decorator, EntityController $entityController)
	{
		$this->entityController = $entityController;

		parent::__construct($decorator);
	}

	public function indexForEntity($id)
	{
		if ( ! $this->canView()) App::abort(403);

		$report = new RevisionReport($this->decorator, $id);

		$listingUrl = [$this->entityController->getActionName('show'), [
			'type' => Entity::find($id)->template->type_alias
		]];

		$report->setReportActions([
			$this->actions->custom(
				new Link($listingUrl, 'Back to Listing', 'fa fa-list-alt', [
					'class' => 'btn-default pull-right',
				]),
				new IsValid([$this->entityController, 'canView'])
			)
		]);

		$report->setRowActions($this->getRowActions());

		return $report->render();
	}

	public function revert($id)
	{
		$modelInstance = $this->decorator->findInstance($id);
		$entity = $modelInstance->entity;

		if ($entity->currentRevision) {
			$entity->currentRevision()->associate($modelInstance);
			$entity->save();
		}

		Revision::whereEntityId($entity->id)->where('created_at', '>', $modelInstance->created_at)->delete();

		$response = $this->getUpdateResponse($modelInstance);
		$response->with('model.updated', sprintf(
			'Successfully reverted "%s"',
			$this->decorator->getLabel($modelInstance)
		));

		return $response;
	}

	protected function getRowActions()
	{
		return [
			$this->actions->custom(
				new Link(new CustomUrl(function($instance) {
					return action($this->entityController->getActionName('edit'), [
						'entity_id' => $instance->entity->id,
						'revision_id' => $instance->id
					]);
				}), 'View', 'fa fa-eye', ['class' => 'btn-info']),
				new IsValid([$this->entityController, 'canEdit'])
			),
			$this->actions->custom(
				new Form($this->getActionName('revert'), 'Revert', 'fa fa-undo', [
					'class' => 'btn-warning',
					'warn' => 'Are you sure you wish to revert to this revision? This action cannot be undone.'
				]),
				new IsValid([$this->entityController, 'canEdit'])
			),
		];
	}

	public function getSuccessResponse($instance)
	{
		return Redirect::action($this->getActionName('indexForEntity'), ['entity_id' => $instance->entity->id]);
	}
}
