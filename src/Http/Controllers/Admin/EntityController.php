<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Button;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Url;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Repositories\Contracts\EntityRepository;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\NestedType;
use Bozboz\Permissions\RuleStack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends ModelAdminController
{
	protected $useActions = true;

	protected $repository;
	protected $type;

	public function __construct(EntityDecorator $decorator, EntityRepository $repository)
	{
		parent::__construct($decorator);
		$this->repository = $repository;
	}

	public function show($type)
	{
		$this->type = app('EntityMapper')->get($type);
		$this->decorator = $this->type->getDecorator();
		$this->decorator->setType($this->type);

		if (!$this->type) {
			throw new NotFoundHttpException;
		}

		if ( ! $this->canShow($type)) App::abort(403);

		$report = $this->getListingReport();

		$report->injectValues(Input::all());

		$report->setReportActions($this->getReportActions());
		$report->setRowActions($this->getRowActions());

		return $report->render();
	}

	/**
	 * Get an instance of a report to display the model listing
	 *
	 * @return Bozboz\Admin\Reports\NestedReport
	 */
	protected function getListingReport()
	{
		return $this->type->getReport($this->decorator, request()->get('per-page'));
	}

	protected function getTemplateOptions()
	{
		if ( ! $this->type) {
			return collect();
		}

		static $options;

		if ( ! $options) {
			$options = Template::whereTypeAlias($this->type->alias)
				->where(function($query) {
					$query->whereHas('entities', function($query) {
						$query->selectRaw('COUNT(*) as count');
						$query->havingRaw('entity_templates.max_uses > count');
						$query->orHavingRaw('entity_templates.max_uses IS NULL');
					});
					$query->orWhere(function($query) {
						$query->doesntHave('entities');
					});
				})
				->orderBy('name')
				->get();
		}

		return $options;
	}

	/**
	 * Return an array of actions the report can perform
	 *
	 * @return array
	 */
	protected function getReportActions()
	{
		return [
			$this->actions->dropdown($this->getTemplateOptions()->map(function($template) {
				return $this->actions->custom(
					new Link([$this->getActionName('createOfType'), [$template->type_alias, $template->alias]], $template->name),
					new IsValid([$this, 'canCreate'])
				);
			})->all(), 'New', 'fa fa-plus', [
				'class' => 'btn-success',
			], [
				'class' => 'pull-right space-left',
			]),
			$this->actions->custom(
				new Link(
					['\Bozboz\Jam\Http\Controllers\Admin\EntityArchiveController@show', $this->type->alias],
					'View Archive', 'fa fa-archive',
					['class' => 'pull-right space-left btn btn-warning']
				),
				new IsValid([$this, 'canViewArchive'])
			),
		];
	}

	protected function getRevisionController()
	{
		return app(EntityRevisionController::class);
	}

	/**
	 * Return an array of actions each row can perform
	 *
	 * @return array
	 */
	protected function getRowActions()
	{
		$entityRevisionController = $this->getRevisionController();

		return [
			$this->actions->publish([
				$this->actions->custom(
					new Link($entityRevisionController->getActionName('indexForEntity'), 'History', 'fa fa-history'),
					new IsValid([$entityRevisionController, 'canView'])
				),
				$this->actions->custom(
					new Link($this->getActionName('duplicate'), 'Duplicate', 'fa fa-copy'),
					new IsValid([$this, 'canDuplicate'])
				),
				$this->actions->custom(
					new Form($this->getActionName('publish'), 'Publish'),
					new IsValid([$this, 'canPublishFromRow'])
				),
				$this->actions->custom(
					new Form($this->getActionName('unpublish'), 'Hide'),
					new IsValid([$this, 'canHide'])
				),
				// $this->actions->custom(
				// 	new Form($this->getActionName('schedule'), 'Schedule', null, [], [
				// 		'class' => 'js-datepicker-popup',
				// 	]),
				// 	new IsValid([$this, 'canSchedule'])
				// )
			]),
			$this->actions->dropdown($this->getTemplateOptions()->map(function($template) {
				return $this->actions->custom(
					new Link([$this->getActionName('createOfTypeForParent'), [$template->type_alias, $template->alias]], $template->name),
					new IsValid([$this, 'canCreateForParent'])
				);
			})->all(), 'New Child', 'fa fa-plus', [
				'class' => 'btn-default btn-sm',
			]),
			$this->actions->edit(
				$this->getEditAction(),
				[$this, 'canEdit']
			),
			$this->actions->custom(
				new Form($this->getActionName('destroy'), 'Archive', 'fa fa-archive', [
					'class' => 'btn-danger btn-sm',
					'data-warn' => 'Are you sure you want to archive?'
				], [
					'method' => 'DELETE'
				]),

				new IsValid([$this, 'canDestroy'])
			),
		];
	}

	public function getFormActions($instance)
	{
		$publishOptions = [
			$this->actions->custom(
				new Button('Publish', 'fa fa-save', [
					'type' => 'submit',
					'name' => 'submit',
					'value' => json_encode([
						'after_save' => 'continue',
						'status' => 'publish'
					]),
					'class' => 'btn-success btn'
				]),
				new IsValid([$this, 'canPublish'])
			),
			$this->actions->custom(
				new Button('Publish and Exit', null, [
					'type' => 'submit',
					'name' => 'submit',
					'value' => json_encode([
						'after_save' => 'exit',
						'status' => 'publish'
					]),
				]),
				new IsValid([$this, 'canPublish'])
			),
			$this->actions->custom(
				new Button('Publish and Create Another', null, [
					'type' => 'submit',
					'name' => 'submit',
					'value' => json_encode([
						'after_save' => 'create_another',
						'status' => 'publish'
					]),
				]),
				new IsValid([$this, 'canPublish'])
			),
		];
		$draftOptions = [
			$this->actions->submit('Save as Draft', 'fa fa-pencil-square-o', [
				'name' => 'submit',
				'value' => json_encode([
					'after_save' => 'continue',
					'status' => 'draft'
				]),
				'class' => 'btn-warning btn'
			]),
			$this->actions->submit('Save as Draft and Exit', null, [
				'name' => 'submit',
				'value' => json_encode([
					'after_save' => 'exit',
					'status' => 'draft'
				]),
			]),
			$this->actions->submit('Save as Draft and Create Another', null, [
				'name' => 'submit',
				'value' => json_encode([
					'after_save' => 'create_another',
					'status' => 'draft'
				]),
			]),
		];
		return [
			$this->actions->dropdown($publishOptions, 'Publish', '', [
				'class' => 'btn-success',
				'split_button' => true,
			], [
				'class' => 'pull-right space-left',
			]),
			$this->actions->dropdown($draftOptions, 'Save as Draft', '', [
				'class' => 'btn-warning',
				'split_button' => true,
			], [
				'class' => 'pull-right space-left',
			]),
			$this->actions->custom(
				new Link(new Url(url($instance->canonical_path  . '?p=' . md5(date('ymd')))), 'Preview', 'fa fa-eye', [
					'class' => 'btn-info pull-right space-left',
					'target' => '_blank',
				]),
				new IsValid([$this, 'canPreview'])
			),
			$this->actions->custom(
				new Link($this->getActionName('duplicate'), 'Duplicate', 'fa fa-copy', [
					'class' => 'btn-default pull-right space-left',
				]),
				new IsValid([$this, 'canDuplicate'])
			),
			$this->actions->custom(
				new Link(new Url($this->getListingUrl($instance)), 'Back to listing', 'fa fa-list-alt', [
					'class' => 'btn-default pull-right space-left',
				]),
				new IsValid([$this, 'canView'])
			),
		];
	}

	public function createOfType($type, $template)
	{
		$template = Template::whereTypeAlias($type)->whereAlias($template)->first();
		$instance = $this->decorator->newEntityOfType($template);

		if ( ! $this->canCreate($instance)) App::abort(403);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	public function createOfTypeForParent($type, $template, $parentId)
	{
		$template = Template::whereTypeAlias($type)->whereAlias($template)->first();
		$instance = $this->decorator->newEntityOfType($template);

		$instance->parent_id = $parentId;

		if ( ! $this->canCreateForParent($this->decorator->findInstance($parentId))) App::abort(403);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	protected function save($modelInstance, $input)
	{
		if (array_key_exists('submit', $input)) {
			$submit = json_decode($input['submit'], true);
			Input::merge($submit);
			$input['status'] = $submit['status'];
		}
		parent::save($modelInstance, $input);
		$this->repository->newRevision($modelInstance, $input);
	}

	public function publish($id)
	{
		$modelInstance = $this->_changeState($id, new Carbon);

		$response = $this->getUpdateResponse($modelInstance);
		$response->with('model.updated', sprintf(
			'Successfully published "%s"',
			$this->decorator->getLabel($modelInstance)
		));

		return $response;
	}

	public function unpublish($id)
	{
		$modelInstance = $this->decorator->findInstance($id);
		$modelInstance->revision_id = null;
		$modelInstance->save();

		$response = $this->getUpdateResponse($modelInstance);
		$response->with('model.updated', sprintf(
			'Successfully hid "%s"',
			$this->decorator->getLabel($modelInstance)
		));

		return $response;
	}

	public function schedule(Request $request, $id)
	{
		try {
			$scheduleDate = new Carbon($request->get('date'));
		} catch (\Exception $e) {
			Redirect::back()->with('error', 'Invalid schedule date.');
		}

		$modelInstance = $this->_changeState($id, $scheduleDate);

		$response = $this->getUpdateResponse($modelInstance);
		$response->with('model.updated', sprintf(
			'Successfully scheduled "%s"',
			$this->decorator->getLabel($modelInstance)
		));

		return $response;
	}

	private function _changeState($id, $publishedAt)
	{
		DB::beginTransaction();

		$modelInstance = $this->decorator->findInstance($id);
		$revision = $modelInstance->latestRevision();

		$newRevision = $revision->duplicate();

		$newRevision->published_at = $publishedAt;

		$newRevision->expired_at = $newRevision->expired_at > Carbon::now()
			? $newRevision->expired_at
			: null;

		$newRevision->user()->associate(Auth::user());
		$newRevision->save();

		if ($publishedAt) {
			$modelInstance->currentRevision()->associate($newRevision);
			$modelInstance->save();
		} else {
			$modelInstance->currentRevision()->dissociate();
		}

		DB::commit();

		return $modelInstance;
	}

	public function duplicate($id)
	{
		DB::beginTransaction();

		$entity = $this->decorator->findInstance($id);

		$newEntity = $entity->replicate();
		$newEntity->name = 'Copy of - ' . $newEntity->name;
		$newEntity->slug = 'copy-of-' . $newEntity->slug;
		$newEntity->currentRevision()->dissociate();
		$newEntity->save();

		$newRevision = $entity->latestRevision()->duplicate($newEntity);

		Input::merge(['after_save' => 'continue']);

		$response = $this->reEdit($newEntity);
		$response->with('model.updated', sprintf(
			'Successfully duplicated "%s"',
			$this->decorator->getLabel($entity)
		));

		DB::commit();

		return $response;
	}

	/**
	 * The generic response after a successful store/update action.
	 */
	protected function getSuccessResponse($instance)
	{
		return \Redirect::action($this->getActionName('show'), ['type' => $instance->template->type_alias]);
	}

	protected function reEdit($instance)
	{
		if (Input::has('after_save') && Input::get('after_save') === 'create_another') {

			$action = $instance->parent_id ? 'createOfTypeForParent' : 'createOfType';

			return Redirect::action($this->getActionName($action), [
				$instance->template->type_alias,
				$instance->template->alias,
				$instance->parent_id
			]);
		}

		return parent::reEdit($instance);
	}

	protected function getListingUrl($instance)
	{
		if (
			$instance->redirect_back_url
			&& starts_with($instance->redirect_back_url, $this->getSuccessResponse($instance)->getTargetUrl())
		) {
			return $instance->redirect_back_url;
		}
		return action($this->getActionName('show'), ['type' => $instance->template->type_alias]);
	}

	public function canCreateForParent($instance)
	{
		return $this->canCreate() && $instance;
	}

	public function canPreview($instance)
	{
		return $instance->exists && $instance->template->type()->isVisible();
	}

	public function canViewArchive()
	{
		return RuleStack::with('view_entity_archive')->isAllowed();
	}

	public function canDuplicate($instance)
	{
		return $instance->exists && $this->canCreate($instance);
	}

	public function canPublish($instance)
	{
		return RuleStack::with('publish_entity', $instance->template->type_alias)->isAllowed();
	}

	public function canPublishFromRow($instance)
	{
		return $instance->canPublish() && RuleStack::with('publish_entity', $instance->template->type_alias)->isAllowed();
	}

	public function canHide($instance)
	{
		return $instance->canHide() && RuleStack::with('hide_entity', $instance->template->type_alias)->isAllowed();
	}

	public function canSchedule($instance)
	{
		return $instance->canSchedule() && RuleStack::with('schedule_entity', $instance->template->type_alias)->isAllowed();
	}

	protected function createPermissions($stack, $instance)
	{
		$stack->add('create_entity_type', $instance ? $instance->template->type_alias : null);
	}

	protected function editPermissions($stack, $instance)
	{
		$stack->add('edit_entity_type', $instance ? $instance->template->type_alias : null);
	}

	protected function deletePermissions($stack, $instance)
	{
		$stack->add('delete_entity_type', $instance ? $instance->template->type_alias : null);
	}

	protected function viewPermissions($stack)
	{
		$stack->add('view_entity_type');
	}

	protected function canShow($type)
	{
		return RuleStack::with('view_anything')->then('view_entity_type', $type)->isAllowed();
	}
}
