<?php

namespace Bozboz\Jam\Entities\Listeners;

class UpdateSearchIndex
{
    public function handle($event)
    {
        $entity = $event->entity;

        $indexer = app($entity->template->type()->search_handler);

        $indexer->index($entity);
    }
}