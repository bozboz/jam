<?php

namespace Bozboz\Jam\Entities\Events;

class EntityDeleted
{
    public $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}