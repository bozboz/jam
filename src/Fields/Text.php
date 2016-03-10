<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class Text extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new TextField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}