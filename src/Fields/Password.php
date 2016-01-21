<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\PasswordField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

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