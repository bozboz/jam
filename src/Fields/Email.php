<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\EmailField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class Email extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new EmailField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}