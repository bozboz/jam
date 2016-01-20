<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;

class BelongsToEntityField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new BelongsToField($decorator, $this->getValue($value), [
				'name' => $this->getInputName(),
				'label' => preg_replace('/([A-Z])/', ' $1', studly_case($this->name))
			],
			function($query) {
				if (property_exists($this->options_array, 'type')) {
					$query->whereHas('template.type', function($query) {
						$query->whereId($this->options_array->type);
					});
				}
			}
		);
	}

	public function getOptionFields()
	{
		return [
			new SelectField('options_array[type]', [
				'label' => 'Type',
				'options' => ['' => '- All -']+Type::lists('name', 'id')->toArray()
			]),
			// new SelectField('options[template]', ['options' => ['' => '- All -']+Template::lists('name', 'id')->toArray()]),
		];
	}

	public function getValue(Value $value)
	{
		return $value->belongsTo(Entity::class, 'value');
	}
}