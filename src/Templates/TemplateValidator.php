<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Services\Validators\Validator;

class TemplateValidator extends Validator
{
	protected $rules = [
		'name' => 'required',
		'type_alias' => 'required'
	];
}
