<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class BelongsToManyEntities extends BelongsToEntity
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToManyField($decorator, $this->getValue($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ],
            function($query) {
                if (property_exists($this->options_array, 'template')) {
                    $query->whereHas('template', function($query) {
                        $query->whereId($this->options_array->template);
                    });
                } elseif (property_exists($this->options_array, 'type')) {
                    $query->whereHas('template.type', function($query) {
                        $query->whereId($this->options_array->type);
                    });
                }
            }
        );
    }

    public function injectValue(Entity $entity, Value $value)
    {
        parent::injectValue($entity, $value);
        $relations = $this->getValue($value)->with('CurrentValues')->get()->each(function($entity) {
            $entity->loadCurrentValues();
        });
        $entity->setAttribute($value->key, $relations);
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = parent::injectAdminValue($entity, $revision);
        $entity->setAttribute($this->getInputName(), $this->getValue($value)->getRelatedIds()->all());
    }
}