<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\CreateDropdownAction;
use Bozboz\Admin\Reports\Actions\DropdownDatePopupItem;
use Bozboz\Admin\Reports\Actions\DropdownFormItem;
use Bozboz\Admin\Reports\Actions\DropdownItem;
use Bozboz\Jam\Repositories\Contracts\EntityRepository;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\EntityHistoryAction;
use Bozboz\Jam\Entities\PublishAction;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Templates\Template;
use Bozboz\Permissions\RuleStack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends ModelAdminController
{
	protected $repository;
	protected $type;

	public function __construct(EntityDecorator $decorator, EntityRepository $repository)
	{
		parent::__construct($decorator);
		$this->repository = $repository;
	}

	public function index()
	{
		if (!Input::get('type')) {
			return Redirect::to('/admin');
		}

		$this->type = app('EntityMapper')->get(Input::get('type'));
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
		$options = Template::whereTypeAlias(Input::get('type'))->orderBy('name')->get()->map(function($template) {
				return new DropdownItem(
					[$this->getActionName('createOfType'), $template->alias],
					[$this, 'canCreate'],
					['label' => $template->name]
				);
			});

		return [
			'create' => new CreateDropdownAction($options->all())
		];
	}

	/**
	 * Return an array of actions each row can perform
	 *
	 * @return array
	 */
	protected function getRowActions()
	{
		return [
			'publish' => new PublishAction([
				new DropdownFormItem(
					$this->getActionName('publish'),
					[$this, 'canPublish'],
					[
						'label' => 'Publish',
						'method' => 'PUBLISH',
					]
				),
				new DropdownFormItem(
					$this->getActionName('unpublish'),
					[$this, 'canHide'],
					[
						'label' => 'Hide',
						'method' => 'HIDE',
					]
				),
				new DropdownDatePopupItem(
					$this->getActionName('schedule'),
					[$this, 'canSchedule'],
					[
						'label' => 'Schedule',
						'method' => 'SCHEDULE',
					]
				)
			]),
			'history' => new EntityHistoryAction(
				'\\'.$this->getEntityRevisionController().'@index',
				[app()->make($this->getEntityRevisionController()), 'canView']
			)
		] + parent::getRowActions();
	}

	public function getEntityRevisionController()
	{
		return EntityRevisionController::class;
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

		if (Config::get('jam.revision_history_length')) {
			$pastRevisionsQuery = Revision::whereEntityId($modelInstance->id);
			if ($modelInstance->currentRevision) {
				$pastRevisionsQuery->where('created_at', '<', $modelInstance->currentRevision->created_at);
			}
			Revision::whereIn(
				'id',
				$pastRevisionsQuery->orderBy('created_at', 'desc')
					->withTrashed()
					->skip(Config::get('jam.revision_history_length'))->take(100)
					->pluck('id')
			)->forceDelete();
		}
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
		return \Redirect::action($this->getActionName('index'), ['type' => $instance->template->type_alias]);
	}

	protected function getListingUrl($instance)
	{
		return action($this->getActionName('index'), ['type' => $instance->template->type_alias]);
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
