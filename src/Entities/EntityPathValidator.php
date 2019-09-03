<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Services\Validators\Validator;

class EntityPathValidator extends Validator
{
	protected $rules = [
		'path' => 'required',
	];

	protected $editRules = [
		'path' => 'required'
	];
}
