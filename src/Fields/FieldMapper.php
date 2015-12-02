<?php

namespace Bozboz\Entities\Fields;

class FieldMapper
{
	protected $mapping = [
		'text' => 'Bozboz\Admin\Fields\TextField',
		'textarea' => 'Bozboz\Admin\Fields\TextareaField',
		'htmleditor' => 'Bozboz\Admin\Fields\HTMLEditorField',
		'checkbox' => 'Bozboz\Admin\Fields\CheckboxField',
		'hidden' => 'Bozboz\Admin\Fields\HiddenField',
	];

	public function has($alias)
	{
		return array_key_exists($alias, $this->mapping);
	}

	public function get($alias)
	{
		return $this->mapping[$alias];
	}
}
