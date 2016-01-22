<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\CreateAction;
use Bozboz\Entities\Templates\TemplateDecorator;
use Bozboz\Entities\Templates\TemplateFieldsAction;
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

	public function index()
	{
		if (!Input::get('type_id')) {
			return Redirect::route('admin.entity-types.index');
		}
		return parent::index();
	}

	public function getRowActions()
	{
		return array_merge([
			new TemplateFieldsAction(
				'\\'.EntityTemplateFieldController::class.'@index',
				[$this, 'canEdit']
			)
		], parent::getRowActions());
	}

	/**
	 * Return an array of actions the report can perform
	 *
	 * @return array
	 */
	protected function getReportActions()
	{
		return [
			'create' => new CreateAction(
				[$this->getActionName('createForType'), Input::get('type_id')],
				[$this, 'canCreate']
			)
		];
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
