<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Types\Type;

class TypeSelectField extends SelectField
{
    public function __construct($name, $attributes = [])
    {
        parent::__construct('options_array['.e(strtolower($name)).']', array_merge($attributes, [
            'label' => $name,
            'options' => app('EntityMapper')->getAll()->map(function($type) {
                return ($type->menu_title ?: 'Content') . ' - ' . $type->name;
            })->sort()->prepend('- All -', ''),
            'class' => 'js-entity-type-select form-control select2'
        ]));
    }
}
