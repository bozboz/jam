<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Entities\Entities\Value;

class ToggleField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new CheckboxField($this->name);
	}
}