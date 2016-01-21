<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

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