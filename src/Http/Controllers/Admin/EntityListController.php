<?php

namespace Bozboz\Jam\Http\Controllers\Admin;

use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Templates\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Redirect;

class EntityListController extends EntityController
{
    public function createForEntityListField($type, $parentEntity)
    {
        $template = Template::with('fields')->whereAlias($type)->first();
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
        return \Redirect::action('\\' . $this->getEntityController() . '@edit', [$instance->parent_id]);
    }

    protected function getListingUrl($instance)
    {
        return action('\\' . $this->getEntityController() . '@edit', [$instance->parent_id]);
    }
}