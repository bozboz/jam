<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Admin\Reports\Actions\CreateDropdownAction;
use Bozboz\Admin\Reports\Actions\DropdownItem;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Fields\FieldDecorator;
use Bozboz\Jam\Templates\Template;
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
	 * Return an array of actions the report can perform
	 *
	 * @return array
	 */
	protected function getReportActions()
	{
		$options = Field::getMapper()->getAll()->keys()->map(function($type) {
			return new DropdownItem(
				[$this->getActionName('createForTemplate'), 'template_id' => Input::get('template_id'), 'type' => $type],
				[$this, 'canCreate'],
				['label' => $type]
			);
		});

		return [
			'create' => new CreateDropdownAction($options)
		];
	}

	public function createForTemplate($templateId, $type)
	{
		$instance = $this->decorator->newModelInstance(['type_alias' => $type]);

		$template = $this->template->find($templateId);
		$instance->template()->associate($template);

		if (!$instance->type_alias) {
			$instance->type_alias = $type;
		}

		return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
	}

	protected function save($modelInstance, $input)
	{
		parent::save($modelInstance, $input);
		$modelInstance->options()->delete();
		if (array_key_exists('options_array', $input)) {
			$options = [];
			foreach (array_filter($input['options_array'], function($a){return $a !== '';}) as $key => $value) {
				$options[] = [
					'key' => $key,
					'value' => $value
				];
			}
			$modelInstance->options()->createMany($options);
		}
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
