<?php

namespace Bozboz\Jam\Fields;

class FieldMapper
{
	protected $mapping;

	public function register($aliasOrArray, $fieldClass = null)
	{
		if (is_array($aliasOrArray)) {
			foreach ($aliasOrArray as $alias => $fieldClass) {
				$this->register($alias, $fieldClass);
			}
		} else {
			$this->mapping[$aliasOrArray] = $fieldClass;
		}
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
