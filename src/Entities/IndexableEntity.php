<?php

namespace Bozboz\Jam\Entities;

use Carbon\Carbon;
use Spatie\SearchIndex\Searchable;

class IndexableEntity extends Entity implements Searchable
{
    public function make($entity)
    {
        $entity->load('currentRevision')->injectValues();
        $this->attributes = $entity->toArray();
        $this->searchable_id = $entity->id;
        $this->searchable_type = get_class($entity);
    }

    public function getSearchableBody()
    {
        $exclude = $this->getDates();
        $exclude[] = $this->getKeyName();

        return array_except($this->toArray(), $exclude);
    }

    public function getSearchableType()
    {
        return $this->template['type_alias'];
    }

    public function getSearchableId()
    {
        return $this->getKey();
    }

    public function shouldIndex()
    {
        return $this->current_revision && new Carbon($this->current_revision['published_at']) < new Carbon;
    }
}