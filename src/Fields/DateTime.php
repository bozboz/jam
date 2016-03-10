<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class DateTime extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new DateTimeField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}