<?php

namespace Bozboz\Entities\Http\Controllers\Admin;

use Bozboz\Admin\Http\Controllers\ModelAdminController;
use Bozboz\Entities\Fields\Field;
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
		$fieldTypes = array_keys(Field::getMapper()->getAll());
		return array_merge(parent::getReportParams(), [
			'createAction' => $this->getActionName('createForTemplate'),
			'createParams' => ['templateId' => Input::get('template_id')],
			'newButtonPartial' => 'entities::admin.partials.new-template-field',
			'fieldTypes' => $fieldTypes
		]);
	}

	public function createForTemplate($templateId, $type)
	{
		$instance = $this->decorator->newModelInstance(['type_alias' => $type]);

		$template = $this->template->find($templateId);
		$instance->template()->associate($template);
		$instance->type_alias = $type;

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
