<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;

class BelongsToTypeField extends Field
{
	public function getAdminField(EntityDecorator $decorator, Value $value)
	{
		return new BelongsToField($decorator, $this->getValue($value), [
			'name' => $this->getInputName(),
			'label' => preg_replace('/([A-Z])/', ' $1', studly_case($this->name))
		]);
	}

	public function getValue(Value $value)
	{
		return $value->belongsTo(Type::class, 'value');
	}
}