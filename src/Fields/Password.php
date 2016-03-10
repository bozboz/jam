<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\PasswordField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class Password extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new PasswordField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}