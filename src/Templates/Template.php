<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Models\Base;
use Bozboz\Entities\Entity;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Types\Type;

class Template extends Base
{
	protected $table = 'entity_templates';

	protected $fillable = [
		'name',
		'alias',
		'type_id'
	];

	public function getValidator()
	{
		return new TemplateValidator;
	}

	public function fields()
	{
		return $this->hasMany(Field::class);
	}

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function type()
	{
		return $this->belongsTo(Type::class);
	}

	/**
	 * Iterate over a template's fields, and build an array of field instances
	 * found in the FieldMapper lookup.
	 *
	 * @param  Bozboz\Entities\FieldMapper  $mapper
	 * @return array
	 */
	public function getFields(FieldMapper $mapper)
	{
		$fields = [];

		foreach($this->fields as $field) {
			if ($mapper->has($field->type_alias)) {
				$class = $mapper->get($field->type_alias);
				$fields[] = new $class($field->name);
			}
		}

		return $fields;
	}
}
