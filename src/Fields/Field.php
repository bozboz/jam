<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Base\Model;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Fields\Options\Option;
use Bozboz\Entities\Templates\Template;

class Field extends Model implements FieldInterface
{
    protected $table = 'entity_template_fields';

    protected $fillable = [
        'name',
        'validation',
        'template_id',
        'type_alias',
    ];

    protected static $mapper;

    public function getValidator()
    {
        return new FieldValidator;
    }

    public static function setMapper(FieldMapper $mapper)
    {
        static::$mapper = $mapper;
    }

    public static function getMapper()
    {
        return static::$mapper;
    }

    public function getAdminField(EntityDecorator $decorator, Value $value)
    {
        throw new \Exception("Attempting to create admin field for unknown field type", 1);
    }

    public function options()
    {
        return $this->hasMany(Option::class, 'field_id');
    }

    public function getOptionsArrayAttribute()
    {
        return (object) array_column($this->options->toArray(), 'value', 'key');
    }

    public function getOptionFields()
    {
        return [];
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function injectValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first();
        $entity->setValue($value);
        return $value;
    }

    public function getInputName()
    {
        return e($this->name);
    }

    public function saveValue(Revision $revision, $value)
    {
        $fieldValue = [
            'field_id' => $this->id,
            'key' => $this->name,
            'value' => !is_array($value) ? $value : null,
        ];
        $valueObj = $revision->fieldValues()->create($fieldValue);

        return $valueObj;
    }

    public function getValue(Value $value)
    {
        return $value->value;
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
            $mapper = static::getMapper();
            $class = $mapper->get($attributes['type_alias']);
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
}
