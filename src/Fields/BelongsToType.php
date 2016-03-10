<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class BelongsToType extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToField($decorator, $this->getValue($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
        ]);
    }

    public function injectValue(Entity $entity, Revision $revision, $realValue)
    {
        $value = parent::injectValue($entity, $revision, $realValue);

        if (!$realValue) {
            $entity->setAttribute($value->key, $this->getValue($value)->first()->entities->transform(function ($entity, $key) {
                return $entity->setAttribute('path', $entity->paths()->first());
            }));
        }
    }

    public function getValue(Value $value)
    {
        return $value->belongsTo(Type::class, 'value');
    }
}