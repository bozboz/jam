<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\TextField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

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