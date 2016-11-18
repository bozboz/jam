<?php

namespace Bozboz\Jam\Entities;

use Kalnoy\Nestedset\Collection as NestedCollection;

class Collection extends NestedCollection
{
    public function loadFields()
    {
        if (count($this->items) > 0) {
            $query = $this->first()->newQuery()->withFields(func_get_args());
            $this->items = $query->eagerLoadRelations($this->items);
        }

        return $this->injectValues();
    }

    public function injectValues()
    {
        foreach ($this->items as $item) {
            $item->injectValues();
        }
        return $this;
    }
}