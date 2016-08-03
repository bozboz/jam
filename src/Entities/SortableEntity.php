<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;
use Bozboz\Jam\Entities\Events\EntitySorted;
use DB;

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
        DB::beginTransaction();

        $original = $this->original;

        $this->traitSort($before, $after, $parent);
        $this->template->type()->updatePaths($this);

        $this->original = $original;

        event(new EntitySorted($this));

        DB::commit();
    }

    public function isSortable()
    {
        return true;
    }
}