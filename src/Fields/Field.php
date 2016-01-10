<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Base\Model;
use Bozboz\Entities\Entity;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Templates\Template;

class Field extends Model implements FieldInterface
{
	protected $table = 'entity_template_fields';

	protected $fillable = [
		'name',
		'validation',
		'template_id',
		'type_alias'
	];

	public function getValidator()
	{
		return new FieldValidator;
	}

	public function template()
	{
		return $this->belongsTo(Template::class);
	}

	public function injectValue($entity, $revision, $valueKey)
	{
		$value = $revision->fieldValues->where('key', $valueKey)->first();

		$entity->setValue($value->key, $value);
		$entity->setAttribute($value->key, $value->value);

		return $value;
	}

	public function getInputName()
	{
		return e($this->name);
	}

	public function saveValue($revision, $value)
	{
		$fieldValue = [
			'field_id' => $this->id,
			'key' => $this->name,
			'value' => !is_array($value) ? $value : null,
		];
		$valueObj = $revision->fieldValues()->create($fieldValue);

		return $valueObj;
	}
}
