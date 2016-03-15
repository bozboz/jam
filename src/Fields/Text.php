<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Netcarver\Textile\Parser;

class Text extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new TextField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel()
		]);
	}

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