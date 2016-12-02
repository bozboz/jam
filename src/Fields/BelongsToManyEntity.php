<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Entities\Entity;

class BelongsToManyEntity extends SortedBelongsToMany
{
    protected function getRelationModel()
    {
        return Entity::class;
    }

    protected function getPivot()
    {
        return (object)[
            'table' => 'entity_entity',
            'foreign_key' => 'value_id',
            'other_key' => 'entity_id'
        ];
    }

    protected function filterAdminQuery($query, $value) {
        parent::filterAdminQuery($query, $value);

        if (property_exists($this->options_array, 'template')) {
            $query->whereHas('template', function($query) {
                $query->whereId($this->options_array->template);
            });
        } elseif (property_exists($this->options_array, 'type')) {
            $query->ofType($this->options_array->type);
        }
    }

    public function getOptionFields()
    {
        return [
            new TemplateSelectField('Type')
        ];
    }
}