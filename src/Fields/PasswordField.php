<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\PasswordField as PasswordInput;
use Bozboz\Entities\Entities\Value;

class PasswordField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new PasswordInput($this->name);
	}
}