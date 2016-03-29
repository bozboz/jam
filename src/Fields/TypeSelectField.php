<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Types\Type;

class TypeSelectField extends SelectField
{
    public function __construct($name)
    {
        parent::__construct('options_array['.$name.'_type]', [
            'label' => $name,
            'options' => app('EntityMapper')->getAll()->prepend(['- All -', '']),
            'class' => 'js-entity-type-select form-control select2'
        ]);
    }
}