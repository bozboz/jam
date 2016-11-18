<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class BelongsToMany extends BelongsTo
{
    protected $sortable = true;
    protected $relationModel = Entity::class;

    protected function getRelationTable()
    {
        $model = $this->getRelationModel();
        return (new $model)->getTable();
    }

    protected function getRelationModel()
    {
        return $this->relationModel;
    }

    protected function getPivotTable()
    {
        return $this->pivot;
    }

    protected function getPivot()
    {
        return (object)[
            'table' => 'entity_entity',
            'foreign_key' => 'value_id',
            'other_key' => 'entity_id'
        ];
    }

    protected function isSortable()
    {
        return $this->sortable;
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToManyField($decorator, $this->relation($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel(),
                'help_text_title' => $this->help_text_title,
                'help_text' => $this->help_text,
                'data-sortable' => $this->isSortable() ? 1 : 0,
            ],
            function ($query) use ($value) {
                if (property_exists($this->options_array, 'template')) {
                    $query->whereHas('template', function($query) {
                        $query->whereId($this->options_array->template);
                    });
                } elseif (property_exists($this->options_array, 'type')) {
                    $query->ofType($this->options_array->type);
                }

                if ($this->isSortable()) {
                    $pivot = $this->getPivot();
                    $query->leftJoin($pivot->table . ' as pivot_sorting', function($join) use ($value, $pivot) {
                        $join->on('pivot_sorting.' . $pivot->other_key, '=', $this->getRelationTable() . '.id');
                        $join->where('pivot_sorting.' . $pivot->foreign_key, '=', $value->id);
                    });

                    $query->orderBy('pivot_sorting.sorting');
                }
            }
        );
    }

    public function getOptionFields()
    {
        return [
            new TemplateSelectField('Type')
        ];
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = parent::injectAdminValue($entity, $revision);
        $entity->setAttribute($this->getInputName(), $this->relation($value)->getRelatedIds()->all());
    }

    public function relation(Value $value)
    {
        $pivot = $this->getPivot();
        $query = $value->belongsToMany($this->getRelationModel(), $pivot->table, $pivot->foreign_key, $pivot->other_key);
        if ($this->isSortable()) {
            $query->withPivot('sorting')->orderBy('sorting');
        }
        return $query;
    }

    public function saveValue(Revision $revision, $value)
    {
        $valueObj = parent::saveValue($revision, json_encode($value));
        $syncData = [];

        if (is_array($value)) {
            if ($this->isSortable()) {
                foreach($value as $i => $entityId) {
                    $syncData[$entityId] = [
                        'sorting' => $i
                    ];
                }
            } else {
                $syncData = $value;
            }
        }

        $this->relation($valueObj)->sync($syncData);

        return $valueObj;
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {
        if ($this->isSortable()) {
            $syncData = $this->relation($oldValue)->withPivot('sorting')->pluck($this->getPivot()->foreign_key, 'sorting')->toArray();
        } else {
            $syncData = $this->relation($oldValue)->pluck($this->getPivot()->foreign_key)->toArray();
        }
        $this->relation($newValue)->sync($syncData);
    }
}
