<?php

namespace Bozboz\Jam\Entities;

use Kalnoy\Nestedset\Collection as NestedCollection;

class Collection extends NestedCollection
{
    public function loadFields($fields)
    {
        if (count($this->items) > 0) {
            if (is_string($fields)) {
                $fields = func_get_args();
            }

            $query = $this->first()->newQuery()->with(['currentValues' => function($query) use ($fields) {
                $query
                    ->selectFields($fields)
                    ->with('dynamicRelation');
            }]);

            $this->items = $query->eagerLoadRelations($this->items);
        }

        return $this;
    }
}