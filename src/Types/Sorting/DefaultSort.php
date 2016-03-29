<?php

namespace Bozboz\Jam\Types\Sorting;

class DefaultSort
{
	public function sortQuery($query)
	{
		$query->orderBy('_lft');
	}

	public function isSortable()
	{
		return true;
	}
}