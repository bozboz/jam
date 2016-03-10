<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Types\Type;

class TypeDecorator extends ModelAdminDecorator
{
	public function __construct(Type $instance)
	{
		parent::__construct($instance);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $this->getLabel($instance),
			'Generates Paths' => $instance->generate_paths ? '<i class="fa fa-check"></i>' : '',
			'Visible' => $instance->visible ? '<i class="fa fa-check"></i>' : '',
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	/**
	 * @param  array  $attributes
	 * @return Bozboz\Admin\Base\ModelInterface
	 */
	public function newModelInstance($attributes = array())
	{
		return $this->model->newInstance(['visible' => true]);
	}

	public function getFields($instance)
	{
		return [
			new TextField('name'),
			($instance->exists ? new TextField('alias') : null),
			new CheckboxField('visible'),
			new CheckboxField('generate_paths'),
		];
	}

	public function getSyncRelations()
	{
		return ['media'];
	}
}
