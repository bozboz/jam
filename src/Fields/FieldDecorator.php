<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Fields\TemplateField;
use Bozboz\Entities\Templates\Template;
use Illuminate\Database\Eloquent\Builder;
use Input;

class FieldDecorator extends ModelAdminDecorator
{
	protected $adminFieldClass;
	protected $mapper;

	public function __construct(Field $instance, FieldMapper $mapper)
	{
		$this->mapper = $mapper;
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $this->getLabel($instance),
			'Type' => $instance->type_alias,
			'Validation' => $instance->validation
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
			new TextField('type_alias', ['disabled' => 'disabled']),
			new TextField('name'),
			new TextField('validation'),
			new HiddenField('template_id'),
			new HiddenField('type_alias'),
		], $instance->getOptionFields());
	}

	/**
	 * Get a new Field instance based on the type
	 *
	 * @param  array  $attributes
	 * @return Bozboz\Entities\Fields\Field
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
	}

	public function setAdminFieldClass($adminFieldClass)
	{
		$this->adminFieldClass = $adminFieldClass;
	}
}
