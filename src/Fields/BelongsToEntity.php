<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\EntitySelectField;

class BelongsToEntity extends BelongsTo
{
    protected function getRelationModel()
    {
        return Entity::class;
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        if (property_exists($this->options_array, 'entity')) {

            if (property_exists($this->options_array, 'make_parent')) {
                if (!$instance->parent_id) {
                    $instance->parent_id = $this->options_array->entity;
                }
                return new HiddenField($this->getInputName());
            }

            return new HiddenField($this->getInputName(), $this->options_array->entity);
        }
        return parent::getAdminField($instance, $decorator, $value);
    }

    protected function filterAdminQuery($query, $value)
    {
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

    public function relation(Value $value)
    {
        return parent::relation($value)->ordered();
    }
}