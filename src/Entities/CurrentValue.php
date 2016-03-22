<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Fields\Field;

class CurrentValue extends Value
{
    protected $fillable = [
        'key',
        'value',
        'type_alias',
        'options',
    ];

    public function injectValue(Entity $entity)
    {
        $field = (new Field())->newInstance([
            'type_alias' => $this->type_alias,
            'options_array' => $this->options
        ], false);
        $field->injectValue($entity, $this);
    }
}
