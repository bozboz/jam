<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Types\Type;

class TypeSelectField extends SelectField
{
    public function __construct($name)
    {
        parent::__construct('options_array['.e(strtolower($name)).'_type]', [
            'label' => $name,
            'options' => app('EntityMapper')->getAll()->map(function($type) {
                return $type->name;
            })->prepend('- All -', ''),
            'class' => 'js-entity-type-select form-control select2'
        ]);
    }
}