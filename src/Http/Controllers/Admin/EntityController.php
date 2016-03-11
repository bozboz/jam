<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\CreateDropdownAction;
use Bozboz\Admin\Reports\Actions\DropdownDatePopupItem;
use Bozboz\Admin\Reports\Actions\DropdownFormItem;
use Bozboz\Admin\Reports\Actions\DropdownItem;
use Bozboz\Admin\Reports\NestedReport;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\PublishAction;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Bozboz\Permissions\RuleStack;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Input, Redirect, DB;
use Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends ModelAdminController
{
	protected $type;

	public function __construct(EntityDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	public function index()
	{
		if (!Input::get('type')) {
			return Redirect::to('/admin');
		}

		$this->type = Type::with('templates')->where('alias', Input::get('type'))->first();

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
		return new NestedReport($this->decorator);
	}

	/**
	 * Return an array of actions the report can perform
	 *
	 * @return array
	 */
	protected function getReportActions()
	{
		$options = Template::whereHas('type', function($query) {
				$query->whereAlias(Input::get('type'));
			})->orderBy('name')->get()->map(function($template) {
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
			'published' => new PublishAction([
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
			])
		] + parent::getRowActions();
	}

	public function createOfType($type)
	{
		$template = Template::whereAlias($type)->first();
		$instance = $this->decorator->newEntityOfType($template);

		if ( ! $this->canCreate($instance)) App::abort(403);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	public function edit($id)
	{
		$instance = $this->decorator->findInstance($id);
		$instance->loadValues($instance->latestRevision());

		if ( ! $this->canEdit($instance)) App::abort(403);

		return $this->renderFormFor($instance, $this->editView, 'PUT', 'update');
	}

	protected function save($modelInstance, $input)
	{
		parent::save($modelInstance, $input);
		$revision = $modelInstance->newRevision($input);
		if ($revision) {
			$modelInstance->currentRevision()->associate($revision);
			$modelInstance->save();
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
		$modelInstance = $this->_changeState($id, null);

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

		$modelInstance->currentRevision()->associate($newRevision);
		$modelInstance->save();

		DB::commit();

		return $modelInstance;
	}

	/**
	 * The generic response after a successful store/update action.
	 */
	protected function getSuccessResponse($instance)
	{
		return \Redirect::action($this->getActionName('index'), ['type' => $instance->template->type->alias]);
	}

	protected function getListingUrl($instance)
	{
		return action($this->getActionName('index'), ['type' => $instance->template->type->alias]);
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
