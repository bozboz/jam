<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;

class SortableEntity extends Entity implements Sortable
{
	use NestedSortableTrait;

	public function sortBy()
	{
		return '_lft';
	}

	public function scopeOrdered($query)
	{
		$query->defaultOrder();
	}
}