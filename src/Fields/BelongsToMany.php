<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

abstract class BelongsToMany extends BelongsTo
{
    /**
     * Get relation model
     *
     * @return string
     */
    abstract protected function getRelationModel();

    /**
     * Get pivot table config
     *
     * @return object [
     *     'table' => '',
     *     'foreign_key' => '',
     *     'other_key' => '',
     * ]
     */
    abstract protected function getPivot();

    protected function getRelationTable()
    {
        $model = $this->getRelationModel();
        return (new $model)->getTable();
    }

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToManyField(
            $decorator,
            $this->relation($value),
            $this->getAdminFieldAttributes(),
            function($query) use ($value) {
                $this->filterAdminQuery($query, $value);
            }
        );
    }

    protected function getAdminFieldAttributes()
    {
        return [
            'name' => $this->getInputName(),
            'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
        ];
    }

    protected function filterAdminQuery($query, $value) {}

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = parent::injectAdminValue($entity, $revision);
        $entity->setAttribute($this->getInputName(), $this->relation($value)->getRelatedIds()->all());
    }

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $this->injectValue($entity, $value);
        $entity->setAttribute(
            $value->key,
            $entity->getAttribute($value->key)->map(function($entity) {
                return $entity->name;
            })->implode("\n")
        );
        return $value;
    }

    public function relation(Value $value)
    {
        $pivot = $this->getPivot();
        $query = $value->belongsToMany($this->getRelationModel(), $pivot->table, $pivot->foreign_key, $pivot->other_key);
        return $query->active();
    }

    public function saveValue(Revision $revision, $value)
    {
        $valueObj = parent::saveValue($revision, json_encode($value));
        $syncData = [];

        if (is_array($value)) {
            $syncData = $value;
        }

        $this->relation($valueObj)->sync($syncData);

        return $valueObj;
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {
        $syncData = $this->relation($oldValue)->pluck($this->getPivot()->foreign_key)->toArray();
        $this->relation($newValue)->sync($syncData);
    }
}