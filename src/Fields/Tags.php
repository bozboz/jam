<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;

abstract class Tags extends BelongsToMany
{
    protected function getAdminFieldAttributes()
    {
        return array_merge(parent::getAdminFieldAttributes(), [
            'key' => 'name',
            'data-tags' => 'true',
        ]);
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = $this->parentInjectAdminValue($entity, $revision);
        $entity->setAttribute($this->getInputName(), $this->relation($value)->get()->pluck('name')->all());
    }

    public function saveValue(Revision $revision, $value)
    {
        $model = $this->getRelationModel();
        $tags = collect($value)->map(function($value) use ($model) {
            return (new $model)->firstOrCreate([
                'name' => $value
            ])->id;
        });
        $valueObj = $this->parentSaveValue($revision, json_encode($tags));

        $this->relation($valueObj)->sync($tags->all());

        return $valueObj;
    }
}
