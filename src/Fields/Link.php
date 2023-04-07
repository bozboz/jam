<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Fields\AdminFields\LinkField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Netcarver\Textile\Parser;

class Link extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new LinkField([
            'tab' => $this->getTab(),
            'name' => $this->getInputName(),
            'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
        ]);
    }

    public function getValue(Value $value)
    {
        return $value->value ? json_decode($value->value) : null;
    }

    public function saveValue(Revision $revision, $value)
    {
        parent::saveValue($revision, json_encode($value));
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $entity->setAttribute($value->key, json_decode($value->value));
        $entity->setValue($value->key, $value);
        return $value;
    }
}
