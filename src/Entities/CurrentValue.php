<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\DynamicRelation;
use Bozboz\Jam\Fields\Field;
use Illuminate\Support\Facades\DB;

class CurrentValue extends Value
{
    public function injectValue(Entity $entity)
    {
        $field = Field::getMapper()->get($this->type_alias);
        $field->options_array = array_merge($this->options_array ?: [], $this->getOptions());
        $field->injectValue($entity, $this);
    }

    public function relation()
    {
        return Field::getMapper()->get($this->type_alias)->relation($this);
    }

    public function getOptions()
    {
        return array_combine(explode(',', $this->option_keys), explode(',', $this->option_values));
    }

    public function scopeSelectFields($query, array $fields)
    {
        $first = reset($fields);
        if ($first !== '*') {
            $query->whereIn('entity_values.key', $fields);
        }
    }

    public function scopeForRevisions($query, $revisionIds)
    {
        $query->whereIn('entity_values.revision_id', $revisionIds);
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

    public function dynamicRelation()
    {
        return new DynamicRelation;
    }
}
