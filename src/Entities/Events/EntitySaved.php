<?php

namespace Bozboz\Jam\Entities\Events;

class EntitySaved
{
    public $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}