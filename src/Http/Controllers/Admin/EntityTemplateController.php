<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\Permissions\IsValid;
use Bozboz\Admin\Reports\Actions\Presenters\Link;
use Bozboz\Admin\Reports\Actions\Presenters\Urls\Custom;
use Bozboz\Jam\Templates\TemplateDecorator;
use Bozboz\Jam\Templates\TemplateReport;
use Bozboz\Jam\Types\Type;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class EntityTemplateController extends ModelAdminController
{
	protected $useActions = true;

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
			$this->actions->custom(
				new Link(new Custom(function($instance) {
					return action('\\'.EntityTemplateFieldController::class.'@index', [
						'template_id' => $instance->id
					]);
				}), 'Fields', 'fa fa-list-ul', [
					'class' => 'btn-default'
				]),
				new IsValid([$this, 'canEdit'])
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
			$this->actions->create(
				[$this->getActionName('createForType'), Input::get('type')],
				[$this, 'canCreate'],
				'New Template'
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
