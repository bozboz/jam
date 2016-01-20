<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class DropdownField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new SelectField($this->name);
	}
}