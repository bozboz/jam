<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Decorators\ModelAdminDecorator;
use Bozboz\Admin\Fields\MediaBrowser;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Entities\Types\Type;

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
			'' => link_to_route(
				'admin.entity-templates.index',
				'Edit Templates',
				['type_id' => $instance->id],
				['class' => 'btn btn-default btn-sm', 'style' => 'float:right;']
			)
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getFields($instance)
	{
		return [
			new TextField('name'),
			new TextField('alias'),
			new MediaBrowser($instance->media())
		];
	}
	public function getSyncRelations()
	{
		return ['media'];
	}
}
