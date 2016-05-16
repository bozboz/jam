<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Form;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Repositories\Contracts\EntityRepository;
use Bozboz\Jam\Templates\Template;
use Bozboz\Permissions\RuleStack;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
		$this->decorator->setType($this->type);

		if (!$this->type) {
			throw new NotFoundHttpException;
		}

		return parent::index();
	}

	/**
	 * Get an instance of a report to display the model listing
	 *
	 * @return Bozboz\Admin\Reports\NestedReport
	 */
	protected function getListingReport()
	{
		return $this->type->getReport($this->decorator);
	}

	/**
	 * Return an array of actions the report can perform
	 *
	 * @return array
	 */
	protected function getReportActions()
	{
		$options = Template::whereTypeAlias($this->type->alias)->orderBy('name')->get()->map(function($template) {
			return $this->actions->custom(
				new Link([$this->getActionName('createOfType'), [$template->alias]], $template->name),
				new IsValid([$this, 'canCreate'])
			);
		});

		return [
			$this->actions->dropdown($options->all(), 'New', 'fa fa-plus', [
				'class' => 'btn-success'
			], [
				'class' => 'pull-right'
			])
		];
	}

	/**
	 * Return an array of actions each row can perform
	 *
	 * @return array
	 */
	protected function getRowActions()
	{
		$entityRevisionController = app(EntityRevisionController::class);

		return array_merge([
			$this->actions->publish([
				$this->actions->custom(
					new Form($this->getActionName('publish'), 'Publish'),
					new IsValid([$this, 'canPublish'])
				),
				$this->actions->custom(
					new Form($this->getActionName('unpublish'), 'Hide'),
					new IsValid([$this, 'canHide'])
				),
				$this->actions->custom(
					new Form($this->getActionName('schedule'), 'Schedule', null, [], [
						'class' => 'js-datepicker-popup',
					]),
					new IsValid([$this, 'canSchedule'])
				)
			]),
			$this->actions->custom(
				new Link($entityRevisionController->getActionName('indexForEntity'), 'History', 'fa fa-history', [
					'class' => 'btn-default'
				]),
				new IsValid([$entityRevisionController, 'canView'])
			)
		], parent::getRowActions());
	}

	public function createOfType($type)
	{
		$template = Template::whereAlias($type)->first();
		$instance = $this->decorator->newEntityOfType($template);

		if ( ! $this->canCreate($instance)) App::abort(403);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	protected function save($modelInstance, $input)
	{
		parent::save($modelInstance, $input);
		$revision = $this->repository->newRevision($modelInstance, $input);

		$pastRevisionsQuery = Revision::whereEntityId($modelInstance->id);
		if ($modelInstance->currentRevision) {
			$pastRevisionsQuery->where('created_at', '<', $modelInstance->currentRevision->created_at);
		}
		Revision::whereIn(
			'id',
			$pastRevisionsQuery->orderBy('created_at', 'desc')
				->withTrashed()
				->skip(config('jam.revision_history_length'))->take(100)
				->pluck('id')
		)->forceDelete();
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
			$scheduleDate = new Carbon(Input::get('date'));
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

	/**
	 * The generic response after a successful store/update action.
	 */
	protected function getSuccessResponse($instance)
	{
		return \Redirect::action($this->getActionName('show'), ['type' => $instance->template->type_alias]);
	}

	protected function getListingUrl($instance)
	{
		return action($this->getActionName('show'), ['type' => $instance->template->type_alias]);
	}

	public function canPublish($instance)
	{
		return $instance->canPublish() && RuleStack::with('publish_entity')->isAllowed();
	}

	public function canHide($instance)
	{
		return $instance->canHide() && RuleStack::with('hide_entity')->isAllowed();
	}

	public function canSchedule($instance)
	{
		return $instance->canSchedule() && RuleStack::with('schedule_entity')->isAllowed();
	}
}
