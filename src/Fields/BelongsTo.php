<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class BelongsTo extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        if (property_exists($this->options_array, 'entity')) {

            if (property_exists($this->options_array, 'make_parent')) {
                $instance->parent_id = $this->options_array->entity;
            }

            return new HiddenField($this->getInputName(), $this->options_array->entity);
        }

        return new BelongsToField($decorator, $this->getValue($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ],
            function($query) {
                if (property_exists($this->options_array, 'template')) {
                    $query->whereHas('template', function($query) {
                        $query->whereId($this->options_array->template);
                    });
                } elseif (property_exists($this->options_array, 'type')) {
                    $query->whereHas('template', function($query) {
                        $query->whereTypeAlias($this->options_array->type);
                    });
                }
            }
        );
    }

    public function getInputName()
    {
        if (property_exists($this->options_array, 'make_parent')) {
            return 'parent_id';
        } else {
            return parent::getInputName();
        }
    }

    public function getOptionFields()
    {
        return [
            new CheckboxField([
                'label' => 'Make related entity the parent',
                'name' => 'options_array[make_parent]'
            ]),
            new EntitySelectField('Entity')
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        parent::injectValue($entity, $value);
        $relation = $this->getValue($value)->first();
        $entity->setAttribute($value->key, $relation);
    }

    public function getValue(Value $value)
    {
        return $value->belongsTo(Entity::class, 'foreign_key');
    }

    protected function usesForeignKey()
    {
        return true;
    }
}
