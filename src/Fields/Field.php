<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Base\Model;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Entity;
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

    public function getAdminField(Value $value)
    {
        throw new \Exception("Attempting to create admin field for unknown field type", 1);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool   $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        if (array_key_exists('type_alias', $attributes)) {
            $class = app(FieldMapper::class)->get($attributes['type_alias']);
            $model = new $class((array) $attributes);
        } else {
            $model = new static((array) $attributes);
        }
        $model->exists = $exists;

        return $model;
    }

    /**
     * Create a new model instance that is existing.
     *
     * @param  array  $attributes
     * @param  string|null  $connection
     * @return static
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
    	$newInstanceAttributes = [];
    	$attributes = (array) $attributes;
    	if (array_key_exists('type_alias', $attributes)) {
    		$newInstanceAttributes['type_alias'] = $attributes['type_alias'];
    	}
        $model = $this->newInstance($newInstanceAttributes, true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->connection);

        return $model;
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


interface FieldInterface {}