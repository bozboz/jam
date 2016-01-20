<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\HiddenField as HiddenInput;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class HiddenField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new HiddenInput($this->instance->name);
	}
}