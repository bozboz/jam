<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\CreateAction;
use Bozboz\Jam\Templates\TemplateDecorator;
use Bozboz\Jam\Templates\TemplateFieldsAction;
use Bozboz\Jam\Templates\TemplateReport;
use Bozboz\Jam\Types\Type;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

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
		if (!Input::get('type')) {
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
				[$this->getActionName('createForType'), Input::get('type')],
				[$this, 'canCreate']
			)
		];
	}

	public function createForType($typeAlias)
	{
		$instance = $this->decorator->newModelInstance();

		$instance->type_alias = $typeAlias;

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	protected function getSuccessResponse($instance)
	{
		return Redirect::action($this->getActionName('index'), ['type' => $instance->type_alias]);
	}

	protected function getListingUrl($instance)
	{
		return action($this->getActionName('index'), ['type' => $instance->type_alias]);
	}
}
