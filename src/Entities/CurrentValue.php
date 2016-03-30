<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Fields\Field;
use Illuminate\Support\Facades\DB;

class CurrentValue extends Value
{
    public function injectValue(Entity $entity)
    {
        $field = (new Field())->newInstance([
            'type_alias' => $this->type_alias,
        ], false);
        $field->options_array = array_combine(explode(',', $this->option_keys), explode(',', $this->option_values));
        $field->injectValue($entity, $this);
    }

    public function scopeSelectFields($query, $fields)
    {
        if ($fields[0] !== '*') {
            $query->whereIn('entity_values.key', $fields);
        }
    }

    public function newQuery()
    {
        $builder = parent::newQuery();

        $builder->select(
                'entity_values.*',
                DB::raw("group_concat(entity_template_field_options.key separator ',') as option_keys"),
                DB::raw("group_concat(entity_template_field_options.value separator ',') as option_values")
            )
            ->leftJoin(
                'entity_template_field_options',
                'entity_values.field_id', '=', 'entity_template_field_options.field_id'
            )
            ->groupBy('entity_values.id');

        return $builder;
    }
}
