<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\HTMLEditorField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class HTMLEditor extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new HTMLEditorField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}
}