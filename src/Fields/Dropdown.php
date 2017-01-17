<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextareaField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Value;

class Dropdown extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
	    return new SelectField([
			'name' => $this->getInputName(),
			'label' => $this->getInputLabel(),
            'options' => $this->getSelectOptions(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
		]);
	}

    protected function getSelectOptions()
    {
        $options = collect(explode("\n", $this->getOption('options')))->map(function($option) {
            @list($value, $label) = explode('=>', $option);
            return [
                'label' => trim($label ?: $value),
                'value' => trim($value),
            ];
        });
        return $options->pluck('label', 'value')->prepend('-', '');
    }

    public function getOptionFields()
    {
        return [
            new TextareaField('options_array[options]', [
                'label' => 'Options',
                'help_text_title' => 'Enter options separated by a new line',
                'help_text' => 'Each line will be used as both value and label of the option. If a different value and label is desired then they should be separated with "=>", e.g. "value => label".',
            ]),
        ];
    }
}