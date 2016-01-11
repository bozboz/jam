<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\TextareaField as TextareaInput;
use Bozboz\Entities\Entities\Value;

class TextareaField extends Field
{
	public function getAdminField(Value $value)
	{
	    return new TextareaField($this->name);
	}
}