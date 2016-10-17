<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Services\Validators\Validator;

class FieldValidator extends Validator
{
	protected $rules = [
		'name' => 'required|regex:/^[a-z0-9_]+$/',
		'type_alias' => 'required',
		'template_id' => 'required|exists:entity_templates,id'
	];
}
