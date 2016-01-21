<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class ToggleField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new CheckboxField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}