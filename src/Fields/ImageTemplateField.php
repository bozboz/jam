<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Entities\Entities\Value;

class ImageTemplateField extends TemplateField
{
	public function getAdminField(Value $value)
	{
		return new $this->adminField($value->image(), [
			'name' => $this->instance->name
		]);
	}
}
