<?php

namespace Bozboz\Entities\Fields;

class FieldMapper
{
	protected $mapping;

	public function register($alias, $fieldClass)
	{
		$this->mapping[$alias] = $fieldClass;
	}

	public function has($alias)
	{
		return array_key_exists($alias, $this->mapping);
	}

	public function get($type_alias)
	{
		$mapping = $this->mapping[$type_alias];
		$field = new $mapping;
		return $field;
	}

	public function getAll()
	{
		return $this->mapping;
	}
}
