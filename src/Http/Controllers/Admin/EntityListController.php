<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\EntityList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Redirect;

class EntityListController extends EntityController
{
    protected $formBackActionLabel = 'Back to parent';

    public function createForEntityListField($typeAlias, $templateAlias, $parentEntity)
    {
        $template = Template::with('fields')->whereTypeAlias($typeAlias)->whereAlias($templateAlias)->first();
        $instance = $this->decorator->newEntityOfType($template);

        if ( ! $this->canCreate($instance)) App::abort(403);

        $parent = $this->decorator->findInstance($parentEntity);

        $instance->parent_id = $parent->id;

        return $this->renderFormFor($instance, $this->createView, 'POST', 'store');
    }

    protected function getEntityController()
    {
        return EntityController::class;
    }

    /**
     * The generic response after a successful store/update action.
     */
    protected function getSuccessResponse($instance)
    {
        return Redirect::action($this->getResponseAction($instance), [$instance->parent_id]);
    }

    protected function getListingUrl($instance)
    {
        return action($this->getResponseAction($instance), [$instance->parent_id]);
    }

    /**
     * If the parent of the current entity is an entity list type then use this
     * controller for the response action.
     * @param  Entity $instance
     * @return string
     */
    protected function getResponseAction($instance)
    {
        $parent = $instance->parent;

        $entityListTypes = app('EntityMapper')->getAll(EntityList::class)->map(function($type) {
            return $type->alias;
        });

        if ($entityListTypes->contains($parent->template->type_alias)) {
            return $this->getActionName('edit');
        }

        return '\\' . $this->getEntityController() . '@edit';
    }
}
