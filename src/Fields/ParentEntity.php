<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class ParentEntity extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        if (property_exists($this->options_array, 'entity')) {
            return new HiddenField($this->getInputName(), $this->options_array->entity);
        }

        return new BelongsToField($decorator, $this->getValue($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ],
            function($query) {
                if (property_exists($this->options_array, 'template')) {
                    $query->whereHas('template', function($query) {
                        $query->whereId($this->options_array->template);
                    });
                } elseif (property_exists($this->options_array, 'type')) {
                    $query->whereHas('template', function($query) {
                        $query->whereTypeAlias($this->options_array->type);
                    });
                }
            }
        );
    }

    public function getInputName()
    {
        return 'parent_id';
    }

    public function getOptionFields()
    {
        return [
            new EntitySelectField('Entity')
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        parent::injectValue($entity, $value);
        $relation = $this->getValue($value)->first();
        if ($relation) {
            $repository = app(\Bozboz\Jam\Repositories\Contracts\EntityRepository::class);
            // $repository->loadCurrentValues($relation);
        }
        $entity->setAttribute($value->key, $relation);
    }

    public function getValue(Value $value)
    {
        return $value->belongsTo(Entity::class, 'foreign_key');
    }

    protected function usesForeignKey()
    {
        return true;
    }
}
