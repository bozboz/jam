<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class Hidden extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new HiddenField($this->instance->name);
	}
}