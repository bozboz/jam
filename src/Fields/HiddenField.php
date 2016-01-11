<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\HiddenField as HiddenInput;
use Bozboz\Entities\Entities\Value;

class HiddenField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new HiddenInput($this->instance->name);
	}
}