<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Entities\Fields\FieldDecorator;
use Bozboz\Entities\Templates\Template;
use Input, Redirect;

class EntityTemplateFieldController extends ModelAdminController
{
	private $template;

	public function __construct(FieldDecorator $decorator, Template $template)
	{
		parent::__construct($decorator);
		$this->template = $template;
	}

	public function index()
	{
		if (!Input::get('template_id')) {
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
			'createAction' => $this->getActionName('createForTemplate'),
			'createParams' => [Input::get('template_id')],
		]);
	}

	public function createForTemplate($templateId)
	{
		$instance = $this->decorator->newModelInstance();

		$template = $this->template->find($templateId);
		$instance->template()->associate($template);

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	protected function getSuccessResponse($instance)
	{
		return Redirect::action($this->getActionName('index'), ['template_id' => $instance->template_id]);
	}

	protected function getListingUrl($instance)
	{
		return action($this->getActionName('index'), ['template_id' => $instance->template_id]);
	}
}
