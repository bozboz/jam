<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\Builder;
use Input;

class TemplateDecorator extends ModelAdminDecorator
{
	protected $fieldMapper;

	public function __construct(Template $instance, FieldMapper $fieldMapper)
	{
		$this->fieldMapper = $fieldMapper;
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $this->getLabel($instance),
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getHeading($plural = false)
	{
		return (Input::has('type_id') ? Type::find(Input::get('type_id'))->name . ' ' : '')
		       . parent::getHeading($plural);
	}

	public function getFields($instance)
	{
		return [
			new TextField('name'),
			new TextField('view'),
			new HiddenField('type_id')
		];
	}

	protected function modifyListingQuery(Builder $query)
	{
		$query->whereTypeId(Input::get('type_id'))->orderBy($this->model->sortBy());
	}
}
