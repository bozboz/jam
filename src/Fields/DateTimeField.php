<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\DateTimeField as DateTimeInput;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class DateTimeField extends Field
{
	public function getAdminField(EntityDecorator $decorator, Value $value)
	{
	    return new DateTimeInput($this->name);
	}
}