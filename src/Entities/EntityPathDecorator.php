<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Fields\AddonTextField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Jam\Entities\EntityDecorator;
use Illuminate\Database\Eloquent\Builder;
use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Reports\Filters\HiddenFilter;
use Bozboz\Admin\Reports\Filters\RelationFilter;

class EntityPathDecorator extends ModelAdminDecorator
{
    public function __construct(EntityPath $model)
    {
        parent::__construct($model);
    }

    public function getColumns($instance)
    {
        return [
            'URL' => url($instance->path),
        ];
    }

    public function modifyListingQuery(Builder $query)
    {
        $query->onlyTrashed()->orderBy('path');
    }

    public function getLabel($instance)
    {
        return $instance->path;
    }

    /**
     * @param  int  $id
     * @return Bozboz\Admin\Base\ModelInterface
     */
    public function findInstance($id)
    {
        return $this->model->withTrashed()->whereId($id)->firstOrFail();
    }

    public function getFields($instance)
    {
        return [
            new HiddenField('entity_id'),
            new HiddenField('deleted_at'),
            new AddonTextField('path', ['data-addonText' => url('/').'/']),
        ];
    }

    public function getHeading($plural = false)
    {
        if ($plural) {
            return "URLs for '".Entity::find(request()->input('entity'))->name."'";
        }
        return 'URL';
    }

    public function getListingFilters()
    {
        return [
            new HiddenFilter(new RelationFilter(
                $this->model->entity(),
                app(\Bozboz\Jam\Entities\EntityDecorator::class)
            ))
        ];
    }
}
