<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Jam\Fields\TemplateField;
use Bozboz\Jam\Templates\Template;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Input;

class FieldDecorator extends ModelAdminDecorator
{
	protected $adminFieldClass;

	public function __construct(Field $instance)
	{
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $this->getLabel($instance),
			'Label' => $instance->label,
			'Type' => $instance->getDescriptiveName(),
			'Validation' => $instance->validation,
			'Tab' => $instance->tab,
			'Options' => $instance->options->pluck('value', 'key')->map(function($value, $key) {
				return "$key: " . str_limit(str_replace("\n", ', ', $value));
			})->implode(' / '),
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getHeading($plural = false)
	{
		return (Input::get('template_id') ? Template::find(Input::get('template_id'))->name . ' ' : '')
			   . parent::getHeading($plural);
	}

	public function getFields($instance)
	{
		return array_merge([
			new SelectField('type_alias', [
				'options' => $this->getTypeOptions(),
				'help_text_title' => 'WARNING:',
				'help_text' => 'Be VERY careful when changing field types, you can end up breaking things quite easily.',
			]),
			// new SelectField('type_alias', ['disabled' => 'disabled']),
			new TextField('name'),
			new TextField('label', ['help_text' => '(optional) name will be used otherwise']),
			new TextField('validation'),
			new TextField('help_text_title'),
			new TextareaField('help_text'),
			//new TextField('tab', ['help_text' => '(optional) if present this field will be in a tab']), // Will be used to put this field in a tab
			new SelectField('tab', [
				'options' => $this->getTabOptions($instance),
				'help_text' => '(optional) if present this field will be in a tab',
			]),
			new HiddenField('template_id'),
			// new HiddenField('type_alias'),
		], $instance->getOptionFields());
	}

	protected function getTabOptions($instance)
    {
        $tabs = $instance->template->getTabsForForm();
		return ['' => 'Default']+$tabs;
    }

	protected function getTypeOptions()
	{
		$types = Field::getMapper()->getAll()->map(function($field, $alias) {
			return $field::getDescriptiveName();
		})->all();
		return ['' => '- Select -']+$types;
	}

	/**
	 * Get a new Field instance based on the type
	 *
	 * @param  array  $attributes
	 * @return Bozboz\Jam\Fields\Field
	 */
	public function newModelInstance($attributes = [])
	{
		$newInstanceAttributes = [];
		$attributes = (array) $attributes;
		if (array_key_exists('type_alias', $attributes)) {
			$newInstanceAttributes['type_alias'] = $attributes['type_alias'];
		}
		return $this->model->newInstance($newInstanceAttributes);
	}

	protected function modifyListingQuery(Builder $query)
	{
		$query->whereTemplateId(Input::get('template_id'));
		$query->orderBy('sorting');
	}

	public function setAdminFieldClass($adminFieldClass)
	{
		$this->adminFieldClass = $adminFieldClass;
	}
}
