<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\PasswordField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;

class Password extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new PasswordField([
            'tab' => $this->getTab(),
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
		]);
	}

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $entity->setAttribute($value->key, str_repeat('*', strlen($value->value)));
        return $value;
    }
}
