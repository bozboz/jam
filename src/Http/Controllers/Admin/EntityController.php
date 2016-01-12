<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Report;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Session;
use Input, Redirect, DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityController extends ModelAdminController
{
	public function __construct(EntityDecorator $decorator)
	{
		parent::__construct($decorator);
	}

	public function index()
	{
		if (!Input::get('type')) {
			return Redirect::to('/admin');
		}
		return parent::index();
	}

	/**
	 * Return an array of params the report requires to render
	 *
	 * @return array
	 */
	protected function getReportParams()
	{
		if (Input::get('type')) {
			$type = Type::where('alias', Input::get('type'))->first();

			if (!$type) {
				throw new NotFoundHttpException;
			}

			$templates = $type->templates()->orderBy('name')->get();
		} else {
			$type = null;
			$templates = Template::orderBy('name')->get();
		}
		$params = [
			'type' => $type,
			'templates' => $templates,

			'createAction' => $this->getActionName('createOfType'),
			'newButtonPartial' => 'entities::admin.partials.new-entity'
		];
		if ($type) {
			$params['heading'] = $type->name;
		}
		return array_merge(parent::getReportParams(), $params);
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
		$instance->loadValues();

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
