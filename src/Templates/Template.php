<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Models\Base;
use Bozboz\Entities\Entity;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\FieldMapper;

class Template extends Base
{
	protected $table = 'entity_templates';

	public function getValidator()
	{
		return new TemplateValidator(
			$this->fields()->lists('pivot.validation', 'name')
		);
	}

	public function fields()
	{
		return $this->belongsToMany(Field::class, 'entity_template_fields')->withPivot('name', 'validation');
	}

	public function entity()
	{
		return $this->belongsTo(Entity::class);
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
			if ($mapper->has($field->alias)) {
				$class = $mapper->get($field->alias);
				$fields[] = new $class($field->pivot->name);
			}
		}

		return $fields;
	}
}
