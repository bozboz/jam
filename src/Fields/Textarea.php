<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class Textarea extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new TextareaField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}