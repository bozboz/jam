<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;

class SortableEntity extends Entity implements Sortable
{
    use NestedSortableTrait {
        sort as traitSort;
    }

    public function sortBy()
    {
        return '_lft';
    }

    public function scopeOrdered($query)
    {
        $query->defaultOrder();
    }

    public function sort($before, $after, $parent)
    {
        $this->traitSort($before, $after, $parent);
        static::$linkBuilder->updatePaths($this);
    }
}