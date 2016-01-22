<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\CreateDropdownAction;
use Bozboz\Admin\Reports\Actions\DropdownItem;
use Bozboz\Admin\Reports\NestedReport;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
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
			'create' => new CreateDropdownAction($options)
		];
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
		$modelInstance->newRevision($input);
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
}
