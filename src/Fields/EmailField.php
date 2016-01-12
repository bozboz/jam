<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\EmailField as EmailInput;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class EmailField extends Field
{
	public function getAdminField(EntityDecorator $decorator, Value $value)
	{
	    return new EmailInput($this->name);
	}
}