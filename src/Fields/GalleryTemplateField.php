<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Entities\Entities\Value;

class GalleryTemplateField extends TemplateField
{
	public function getAdminField(Value $value)
	{
		return new $this->adminField($value->gallery(), [
			'name' => e($this->instance->name).'_relationship',
			'label' => preg_replace('/([A-Z])/', ' $1', studly_case($this->instance->name))
		]);
	}
}
