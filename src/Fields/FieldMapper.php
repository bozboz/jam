<?php

namespace Bozboz\Entities\Fields;

class FieldMapper implements FieldMapperInterface
{
	public function __construct()
	{
		$this->mapping = config('entities.field-map');
	}

	public function has($alias)
	{
		return array_key_exists($alias, $this->mapping);
	}

	public function get($alias)
	{
		return $this->mapping[$alias];
	}
}
