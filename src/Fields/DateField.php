<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\DateField as DateInput;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class DateField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new DateInput($this->name);
	}
}