<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\DateField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class Date extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new DateField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}