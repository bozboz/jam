<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\Contracts\Indexable;
use Carbon\Carbon;

class IndexableEntity extends Entity implements Indexable
{
    public function make($entity)
    {
        $entity->injectValues();
        $this->attributes = $entity->toArray();
        $this->attributes['canonical_path'] = "/$entity->canonical_path";
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
                    'path' => "/$entity->canonical_path",
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
        return $this->currentRevision
            && new Carbon($this->currentRevision['published_at']) < new Carbon
            && array_key_exists('canonical_path', $this->attributes);
    }

    public function getCanonicalPathAttribute()
    {
        return array_key_exists('canonical_path', $this->attributes) ? $this->attributes['canonical_path'] : null;
    }
}