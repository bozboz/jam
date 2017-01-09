<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class Hidden extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new HiddenField($this->name, $this->getOption('value'));
    }

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        return false;
    }

    public function getOptionFields()
    {
        return [
            new TextField([
                'label' => 'Value',
                'name' => "options_array[value]"
            ])
        ];
    }
}