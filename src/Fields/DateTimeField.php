<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\DateTimeField as DateTimeInput;
use Bozboz\Entities\Entities\Value;

class DateTimeField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new DateTimeInput($this->name);
	}
}