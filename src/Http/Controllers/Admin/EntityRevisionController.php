<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Custom as CustomUrl;
use Bozboz\Jam\Entities\CurrentValue;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\RevisionDecorator;
use Bozboz\Jam\Entities\RevisionReport;
use Illuminate\Http\Request;
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

		$listingUrl = $this->getListingUrl(Entity::find($id));

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

	public function getListingUrl($entity)
	{
		return [$this->entityController->getActionName('show'), [
			'type' => $entity->template->type_alias
		]];
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
				new IsValid([$this, 'canEdit'])
			),
			$this->actions->dropdown([
				$this->actions->custom(
					new Link(new CustomUrl(function($instance) {
						return action($this->getActionName('diff'), $instance->id);
					}), '...between this and previous revision'),
					new IsValid([$this, 'canView'])
				),
				$this->actions->custom(
					new Link(new CustomUrl(function($instance) {
						return action($this->getActionName('diff'), [$instance->id, 'against-live' => 1]);
					}), '...between this and live revision'),
					new IsValid([$this, 'canDiffLive'])
				)
			], 'Show Changes', 'fa fa-files-o', ['class' => 'btn-default'], ['class' => 'dropdown-menu-right']),
			$this->actions->custom(
				new Form($this->getActionName('revert'), 'Revert', 'fa fa-undo', [
					'class' => 'btn-sm btn-warning',
					'data-warn' => 'Are you sure you wish to revert to this revision? This action cannot be undone.'
				]),
				new IsValid([$this, 'canRevert'])
			),
		];
	}

	public function diff(Request $request, $revisionId)
	{
		$revision = Revision::find($revisionId);
		if ($request->has('against-live')) {
			$previousRevision = $revision->entity->currentRevision;
		} else {
			$previousRevision = Revision::whereEntityId($revision->entity_id)
				->where('created_at', '<', $revision->created_at)
				->orderBy('created_at', 'desc')->limit(1)->first();
		}

		$entity = $this->loadRevisionForDiff($revision);
		$previousEntity = $previousRevision ? $this->loadRevisionForDiff($previousRevision) : new Entity;

		return view('jam::admin.diff', compact('previousEntity', 'entity', 'revision'));
	}

	private function loadRevisionForDiff($revision)
	{
		$entity = $revision->entity;
		$entity->template->fields()->orderBy('sorting')->get()->each(function($field) use ($entity, $revision) {
			$field->injectDiffValue($entity, $revision);
		});
		return $entity;
	}

	public function getSuccessResponse($instance)
	{
		return Redirect::action($this->getActionName('indexForEntity'), ['entity_id' => $instance->entity->id]);
	}

	public function canRevert($instance)
	{
		return $this->canEdit($instance)
			&& (
				$instance->entity->currentRevision
				&& $instance->entity->currentRevision->created_at != $instance->created_at
			);
	}

	public function canDiffLive($instance)
	{
		return $this->canView() && $instance->entity->currentRevision;
	}

	protected function editPermissions($stack, $instance)
	{
		$stack->add('edit_entity_history', $instance ? $instance->entity->template->type_alias : null);
	}

	protected function viewPermissions($stack)
	{
		$stack->add('view_entity_history');
	}
}
