<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Michelf\Markdown;

class Text extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new TextField([
            'name' => $this->getInputName(),
            'label' => $this->getInputLabel()
        ]);
    }

    public function getValue(Value $value)
    {
        return Markdown::defaultTransform($value->value);
    }
}