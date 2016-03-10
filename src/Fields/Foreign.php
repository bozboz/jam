<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class Foreign extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new HiddenField($this->name);
    }
}