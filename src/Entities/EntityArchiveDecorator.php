<?php

namespace Bozboz\Jam\Entities;

use Illuminate\Database\Eloquent\Builder;

class EntityArchiveDecorator extends EntityDecorator
{
    public function getHeading($plural = false)
    {
        return parent::getHeading($plural) . ' Archive';
    }

    public function getHeadingForInstance($instance)
    {
        return parent::getHeadingForInstance($instance) . ' Archive';
    }

    public function modifyListingQuery(Builder $query)
    {
        parent::modifyListingQuery($query);
        $query->onlyTrashed();
    }

    public function findInstance($id)
    {
        return $this->model->withLatestRevision()->with('currentRevision')->onlyTrashed()->whereId($id)->firstOrFail();
    }
}
