<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Input;

class TemplateDecorator extends ModelAdminDecorator
{
	protected $fieldMapper;

	public function __construct(Template $instance)
	{
		$this->fieldMapper = app('FieldMapper');
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
		return ''//(Input::has('type_id') ? Type::find(Input::get('type_id'))->name . ' ' : '')
		       . parent::getHeading($plural);
	}

	public function getFields($instance)
	{
		return [
			new TextField('name'),
			($instance->exists ? new TextField('alias') : null),
			new TextField('view'),
			new TextField('listing_view'),
			new TextField('listing_fields'),
			new HiddenField('type_alias')
		];
	}

	protected function modifyListingQuery(Builder $query)
	{
		$query->whereTypeAlias(Input::get('type'))->orderBy($this->model->sortBy());
	}
}
