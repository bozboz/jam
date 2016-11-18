<?php

namespace Bozboz\Jam\Entities;

use Kalnoy\Nestedset\Collection as NestedCollection;

class Collection extends NestedCollection
{
    public function loadFields($fields = ['*'])
    {
        if (count($this->items) > 0) {
            if (is_string($fields)) {
                $fields = func_get_args();
            }
            $query = $this->first()->newQuery()->withFields($fields);
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

    public function loadCanonicalPath()
    {
        $query = $this->first()->newQuery()->withCanonicalPath();

        $query->eagerLoadRelations($this->items);

        return $this;
    }
}