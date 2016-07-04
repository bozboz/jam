<?php

namespace Bozboz\Jam\Entities\Listeners;

use Bozboz\Jam\Repositories\Contracts\EntityRepository;

class UpdateSearchIndex
{
    private $repo;

    public function __construct(EntityRepository $repo)
    {
        $this->repo = $repo;
    }

    public function handle($event)
    {
        $entity = $event->entity;

        $indexer = app($entity->template->type()->search_handler);

        $indexer->index($entity);
    }
}