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
        return [
            'name' => $this->name,
            'path' => $this->canonical_path,
            'preview_data' => $this->preview_data,
            'searchable_data' => $this->searchable_data,
            'breadcrumbs' => $this->getAncestors()->map(function($entity) {
                return [
                    'path' => $entity->canonical_path,
                    'name' => $entity->name,
                ];
            }),
            'searchable_id' => $this->searchable_id,
            'searchable_type' => $this->searchable_type,
        ];
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