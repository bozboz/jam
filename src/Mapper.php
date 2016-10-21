<?php

namespace Bozboz\Jam;

class Mapper
{
	protected $mapping;

	public function register($aliasOrArray, $class = null)
	{
		if (is_array($aliasOrArray)) {
			foreach ($aliasOrArray as $alias => $class) {
				$this->register($alias, $class);
			}
		} else {
			$this->mapping[$aliasOrArray] = $class;
		}
	}

	public function has($alias)
	{
		return array_key_exists($alias, $this->mapping);
	}

	public function get($type_alias)
	{
		$mapping = $this->mapping[$type_alias];
		if (is_string($mapping)) {
			$mapping = app($mapping);
		} else {
			$mapping = $mapping;
		}
		$mapping->alias = $type_alias;

		return $mapping;
	}

	public function getAll($filterClass = null)
	{
		return collect($this->mapping)->each(function($map, $alias) {
			if (!is_string($map)) {
				$map->alias = $alias;
			}
			return $map;
		})->filter(function($item) use ($filterClass) {
			if ( ! $filterClass) {
				return true;
			}

			if (is_object($item)) {
				$class = get_class($item);
			} else {
				$class = $item;
			}
			return $class === $filterClass;
		})->sort();
	}
}
