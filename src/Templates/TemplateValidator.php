<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Services\Validators\Validator;

class TemplateValidator extends Validator
{
	protected $rules = [
		'name' => 'required',
		'type_id' => 'required|exists:entity_types,id'
	];
}
