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

    /**
     * Load the fields of a belongs to or belongs to many entity relation
     *
     * @param  string $relation
     * @param  mixed  $fields  Single field name string or array of field names
     * @return $this
     */
    public function loadRelationFields($relation, $fields = ['*'])
    {
        if ($items->isEmpty()) return $this;

        if ( ! key_exists($relation, $this->first()->getAttributes())) {
            $this->loadFields($relation);
        }

        $relations = $this->pluck($relation)->filter();
        if ( ! $relations->first() instanceof Entity) {
            $relations = $relations->flatten();
        }

        (new static($relations->unique()))->loadFields($fields);

        return $this;
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
        if ($this->first()) {
            $query = $this->first()->newQuery()->withCanonicalPath();

            $query->eagerLoadRelations($this->items);
        }

        return $this;
    }
}
