<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

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