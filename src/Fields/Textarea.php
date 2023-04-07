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
                'tab' => $this->getTab(),
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel(),
                'help_text_title' => $this->help_text_title,
                'help_text' => $this->help_text,
            ]);
        } else {
            return new TextareaField([
                'tab' => $this->getTab(),
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel(),
                'help_text_title' => $this->help_text_title,
                'help_text' => $this->help_text,
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
}
