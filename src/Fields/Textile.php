<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\Text;
use Netcarver\Textile\Parser;

class Textile extends Text
{
    public function injectValue(Entity $entity, Revision $revision, $realValue)
    {
        $value = parent::injectValue($entity, $revision, $realValue);

        if (!$realValue) {
            $entity->setAttribute($value->key, $this->getValue($value));
        }
    }

    public function getValue(Value $value)
    {
        $parser = new Parser;
        return $parser->setBlockTags(false)->parse($value->value);
    }
}