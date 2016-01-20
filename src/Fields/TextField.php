<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\TextField as TextInput;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class TextField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new TextInput($this->name);
	}
}