<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\PasswordField as PasswordInput;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class PasswordField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new PasswordInput([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}