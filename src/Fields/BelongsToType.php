<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\TemplateSelectField;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class BelongsToType extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new HiddenField($this->getInputName());
    }

    public function getOptionFields()
    {
        return [new TemplateSelectField('Entity')];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        parent::injectValue($entity, $value);
        $entities = $this->getValue($value);
        if ($entities) {
            $repository = app(\Bozboz\Jam\Repositories\Contracts\EntityRepository::class);
            $repository->loadCurrentListingValues($entities);
        }
        $entity->setAttribute($value->key, $entities);
    }

    public function getValue(Value $value)
    {
        $query = app('EntityMapper')->get($this->getOption('type'))->getEntity()->ofType($this->getOption('type'));
        if ($this->getOption('template')) {
            $query->whereTemplateId($this->getOption('template'));
        }
        return $query->get();
    }
}