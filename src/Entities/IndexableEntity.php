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
        $this->searchable_id = $entity->id;
        $this->searchable_type = get_class($entity);
    }

    public function getSearchableBody()
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'preview_data' => $this->preview_data,
            'searchable_data' => $this->searchable_data,
            'breadcrumbs' => $this->ancestors()->withCanonicalPath()->get()->map(function($entity) {
                return [
                    'path' => '/' . $entity->paths->pluck('path')->first(),
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
            && array_key_exists('path', $this->attributes);
    }
}