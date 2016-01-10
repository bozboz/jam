<?php

namespace Bozboz\Entities\Fields;

class FieldMapper
{
	protected $mapping;

	public function register($alias, $decorator, $adminField)
	{
		$this->mapping[$alias] = [
			'decorator' => $decorator,
			'adminField' => $adminField
		];
	}

	public function has($alias)
	{
		return array_key_exists($alias, $this->mapping);
	}

	public function get(FieldInterface $field)
	{
		debug($field);
		$mapping = $this->mapping[$field->type_alias];
		$decorator = new $mapping['decorator']($field, $mapping['adminField']);
		return $decorator;
	}
}
