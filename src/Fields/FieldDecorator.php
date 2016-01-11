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
		return [
			new TextField('type_alias', ['disabled' => 'disabled']),
			new TextField('name'),
			new TextField('validation'),
			new HiddenField('template_id'),
			new HiddenField('type_alias'),
		];
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
