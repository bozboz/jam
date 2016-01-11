<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\DateField as DateInput;
use Bozboz\Entities\Entities\Value;

class DateField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new DateInput($this->name);
	}
}