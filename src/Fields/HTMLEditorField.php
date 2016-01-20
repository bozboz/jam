<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\HTMLEditorField as HTMLEditorInput;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;

class HTMLEditorField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new HTMLEditorInput($this->name);
	}
}