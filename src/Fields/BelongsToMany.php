<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class BelongsToMany extends BelongsTo
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToManyField($decorator, $this->relation($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel(),
                'help_text_title' => $this->help_text_title,
                'help_text' => $this->help_text,
                'data-sortable' => 1,
            ],
            function ($query) use ($value) {
                if (property_exists($this->options_array, 'template')) {
                    $query->whereHas('template', function($query) {
                        $query->whereId($this->options_array->template);
                    });
                } elseif (property_exists($this->options_array, 'type')) {
                    $query->ofType($this->options_array->type);
                }

                $query->leftJoin('entity_entity', function($join) use ($value) {
                    $join->on('entity_entity.entity_id', '=', 'entities.id');
                    $join->where('entity_entity.value_id', '=', $value->id);
                });

                $query->orderBy('entity_entity.sorting');
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
        return $value->belongsToMany(Entity::class, 'entity_entity')->withPivot('sorting')->orderBy('sorting');
    }

    public function saveValue(Revision $revision, $value)
    {
        $valueObj = parent::saveValue($revision, json_encode($value));

        $syncData = [];
        if (is_array($value)) {
            foreach($value as $i => $entityId) {
                $syncData[$entityId] = [
                    'sorting' => $i
                ];
            }
        }

        $this->relation($valueObj)->sync($syncData);

        return $valueObj;
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {
        $this->relation($newValue)->sync($this->relation($oldValue)->pluck('entity_id')->toArray());
    }
}
