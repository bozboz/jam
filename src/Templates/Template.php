<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Models\Base;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;
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
	public function getFields(FieldMapper $mapper, Entity $instance)
	{
		$fields = [];

		$instance->loadValues(new Revision);

		foreach($this->fields as $field) {
			if ($mapper->has($field->type_alias)) {
				$class = $mapper->get($field->type_alias);
				$fieldName = $field->name;

				switch ($field->type_alias) {
					case 'image':
						$value = $instance->values ? $instance->values->$fieldName : (new Value);
						$fields[] = new $class($value->image(), [
							'name' => $fieldName
						]);
					break;

					case 'gallery':
						$value = $instance->values ? $instance->values->$fieldName : (new Value);
						$fields[] = new $class($value->gallery(), [
							'name' => $fieldName.'_relationship',
							'label' => $fieldName
						]);
					break;

					default:
						$fields[] = new $class($field->name);
					break;
				}
			}
		}

		return $fields;
	}
}
