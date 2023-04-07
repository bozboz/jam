<?php

namespace Bozboz\Jam\Fields;

use Carbon\Carbon;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Admin\Fields\DateField;
use Bozboz\Jam\Entities\EntityDecorator;

class Date extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new DateField([
            'tab' => $this->getTab(),
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
		]);
	}

    public function getValue(Value $value)
    {
        return $value->value ? new Carbon($value->value) : null;
    }
}
