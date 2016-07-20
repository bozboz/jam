<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\CheckboxField;
use Bozboz\Admin\Fields\HTMLEditorField;
use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;
use Netcarver\Textile\Parser;

class Textarea extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
        if ($this->getOption('wysiwyg')) {
            return new HTMLEditorField([
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ]);
        } else {
            return new TextareaField([
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ]);
        }
	}

    public function getOptionFields()
    {
        return [
            new CheckboxField([
                'name' => 'options_array[wysiwyg]',
                'label' => 'WYSIWYG'
            ])
        ];
    }

    public function getValue(Value $value)
    {
        if ( $this->getOption('wysiwyg')) {
            return $value->value;
        } else {
            return nl2br($value->value);
        }
    }
}