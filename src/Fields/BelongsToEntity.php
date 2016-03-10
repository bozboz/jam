<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class BelongsToEntity extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new BelongsToField($decorator, $this->getValue($value), [
				'name' => $this->getInputName(),
				'label' => $this->getInputLabel()
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