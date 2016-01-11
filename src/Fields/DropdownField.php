<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Entities\Entities\Value;

class DropdownField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new SelectField($this->name);
	}
}