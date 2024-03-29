<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Base\Sorting\Sortable;
use Bozboz\Admin\Base\Sorting\SortableTrait;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\Contracts\Field as FieldInterface;
use Bozboz\Jam\Fields\Options\Option;
use Bozboz\Jam\Mapper;
use Bozboz\Jam\Revisionable;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Templates\TemplateRevision;
use Illuminate\Database\Eloquent\Collection;

class Field extends Model implements FieldInterface, Sortable
{
    use SortableTrait;
    use Revisionable;

    protected $table = 'entity_template_fields';

    protected $fillable = [
        'id',
        'name',
        'label',
        'validation',
        'template_id',
        'type_alias',
        'help_text_title',
        'help_text',
        'sorting',
        'tab',
    ];

    protected $nullable = [
        'validation',
        'help_text_title',
        'help_text',
        'label',
        'tab',
    ];

    protected static $mapper;

    public function getValidator()
    {
        return new FieldValidator;
    }

    public function sortBy()
    {
        return 'sorting';
    }

    protected static function sortPrependOnCreate()
    {
        return false;
    }

    public static function getDescriptiveName()
    {
        return preg_replace('/([A-Z]*)([A-Z])/', '$1 $2', class_basename(static::class));
    }

    public function scopeModifySortingQuery($query, $instance)
    {
        $query->where('template_id', $instance->template_id);
    }

    public static function setMapper(Mapper $mapper)
    {
        static::$mapper = $mapper;
    }

    public static function getMapper()
    {
        return static::$mapper;
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        throw new \Exception("Attempting to create admin field for unknown field type", 1);
    }

    public function options()
    {
        return $this->hasMany(Option::class, 'field_id');
    }

    public function getOption($key)
    {
        if (array_key_exists('options_array', $this->attributes) && array_key_exists($key, $this->attributes['options_array'])) {
            return $this->attributes['options_array'][$key];
        } else {
            return $this->options->where('key', $key)->pluck('value')->first();
        }
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

    public function injectValue(Entity $entity, Value $value)
    {
        $entity->setAttribute($value->key, $this->getValue($value));
        return $value;
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $entity->setAttribute($value->key, $value->value);
        $entity->setValue($value->key, $value);
        return $value;
    }

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $entity->setAttribute($value->key, strip_tags(
            preg_replace('/<\/(p|h\d|ol|ul|pre|address|blockquote|dl|div|fieldset|form|hr|noscript|table)[^>]*>/', "\n", $value->value))
        );
        return $value;
    }

    public function getTab()
    {
        return $this->tab;
    }

    public function getInputName()
    {
        return e($this->name);
    }

    public function getInputLabel()
    {
        return ($this->label ?: preg_replace('/([A-Z])/', ' $1', studly_case($this->name)))
            . (str_contains($this->validation, 'required') ? ' *' : '');
    }

    public function saveValue(Revision $revision, $value)
    {
        $fieldValue = [
            'type_alias' => $this->type_alias,
            'field_id' => $this->id,
            'key' => $this->name,
            'value' => !is_array($value) ? $value : null,
            'tab' => $this->tab,
            'foreign_key' => $this->usesForeignKey() ? ($value ?: null) : null
        ];
        $valueObj = $revision->fieldValues()->create($fieldValue);

        return $valueObj;
    }

    protected function usesForeignKey()
    {
        return false;
    }

    public function getValue(Value $value)
    {
        if ($value->relationLoaded($value->key)) {
            return $value->getRelation($value->key);
        } else {
            $relation = $this->relation($value);
            return $relation ? $relation->getResults() : $value->value;
        }
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {

    }

    public function saveImmediately()
    {
        return false;
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

    public function relation(Value $value)
    {
        return null;
    }
}
