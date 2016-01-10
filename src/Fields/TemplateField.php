<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Entities\Entities\Value;

class TemplateField
{
	protected $instance;
	protected $adminField;

	public function __construct(Field $instance, $adminField)
	{
		$this->instance = $instance;
		$this->adminField = $adminField;
	}

	public function getAdminField(Value $value)
	{
		return new $this->adminField($this->instance->name);
	}
}
