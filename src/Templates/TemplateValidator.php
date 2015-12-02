<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Services\Validators\Validator;

class TemplateValidator extends Validator
{
	protected $rules = [
		'name' => 'required',
		'slug' => 'required'
	];

	public function __construct(array $rules = [])
	{
		$this->rules += $rules;
	}
}
