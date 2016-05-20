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
        return new BelongsToManyField($decorator, $this->getValue($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ],
            [$this, 'filterAdminFieldQuery']
        );
    }

    public function getOptionFields()
    {
        return [
            new TemplateSelectField('Type')
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        parent::injectValue($entity, $value);
        $repository = app(\Bozboz\Jam\Repositories\Contracts\EntityRepository::class);
        $relations = $repository->loadCurrentListingValues($this->getValue($value)->ordered()->active()->get());
        $entity->setAttribute($value->key, $relations);
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = parent::injectAdminValue($entity, $revision);
        $entity->setAttribute($this->getInputName(), $this->getValue($value)->getRelatedIds()->all());
    }

    public function getValue(Value $value)
    {
        $class = app('EntityMapper')->get($this->getOption('type'))->getEntity();
        return $value->belongsToMany(get_class($class), 'entity_entity');
    }

    public function saveValue(Revision $revision, $value)
    {
        $valueObj = parent::saveValue($revision, json_encode($value));
        $this->getValue($valueObj)->sync($value ?: []);

        return $valueObj;
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {
        $this->getValue($newValue)->sync($this->getValue($oldValue)->pluck('entity_id')->toArray());
    }
}