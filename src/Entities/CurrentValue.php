<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Fields\Field;

class CurrentValue extends Value
{
    protected $field;

    public function injectValue(Entity $entity)
    {
        $this->field->injectValue($entity, $this);
    }

    public function newFromBuilder($attributes = [], $exists = false)
    {
        $instance = parent::newFromBuilder($attributes, $exists);
        $instance->field = (new Field())->newInstance(['type_alias' => $attributes->type_alias], false);
        return $instance;
    }

    public function getForeignKey()
    {
        return 'value_id';
    }
}
