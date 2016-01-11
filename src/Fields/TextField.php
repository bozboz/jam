<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\TextField as TextInput;
use Bozboz\Entities\Entities\Value;

class TextField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new TextInput($this->name);
	}
}