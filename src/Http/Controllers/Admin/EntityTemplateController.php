<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Entities\Templates\TemplateDecorator;
use Bozboz\Entities\Templates\TemplateReport;
use Bozboz\Entities\Types\Type;
use Input, Redirect;

class EntityTemplateController extends ModelAdminController
{
	private $type;

	public function __construct(TemplateDecorator $decorator, Type $type)
	{
		$this->type = $type;
		parent::__construct($decorator);
	}

	/**
	 * Get an instance of a report to display the model listing
	 *
	 * @return Bozboz\Admin\Reports\NestedReport
	 */
	protected function getListingReport()
	{
		return new TemplateReport($this->decorator);
	}

	public function index()
	{
		if (!Input::get('type_id')) {
			return Redirect::route('admin.entity-types.index');
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
		return array_merge(parent::getReportParams(), [
			'createAction' => $this->getActionName('createForType'),
			'createParams' => [Input::get('type_id')],
		]);
	}

	public function createForType($typeId)
	{
		$instance = $this->decorator->newModelInstance();

		$type = $this->type->find($typeId);
		$instance->type()->associate($type);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	protected function getSuccessResponse($instance)
	{
		return Redirect::action($this->getActionName('index'), ['type_id' => $instance->type_id]);
	}

	protected function getListingUrl($instance)
	{
		return action($this->getActionName('index'), ['type_id' => $instance->type_id]);
	}
}
