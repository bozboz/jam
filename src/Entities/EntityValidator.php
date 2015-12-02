<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Services\Validators\Validator;

class EntityValidator extends Validator
{
	protected $rules = [
		'name' => 'required',
	];

	protected $editRules = [
		'slug' => 'required'
	];

	public function __construct(array $rules = [])
	{
		$this->rules += $rules;
	}
}
