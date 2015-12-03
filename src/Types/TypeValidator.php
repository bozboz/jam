<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Services\Validators\Validator;

class TypeValidator extends Validator
{
	protected $rules = [
		'name' => 'required',
		'alias' => 'required',
	];

	protected $editRules = [
	];
}
