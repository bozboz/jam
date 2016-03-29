<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Fields\Field;

class CurrentValue extends Value
{
    protected $fillable = [
        'id',
        'key',
        'value',
        'foreign_key',
        'type_alias',
        'options',
    ];

    public function injectValue(Entity $entity)
    {
        $field = (new Field())->newInstance([
            'type_alias' => $this->type_alias,
        ], false);
        $field->options_array = $this->options;
        $field->injectValue($entity, $this);
    }
}
