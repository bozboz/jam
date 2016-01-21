<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\EmailField as EmailInput;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class EmailField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new EmailInput([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}