<?php

namespace Bozboz\Jam\Entities\Events;

class EntitySorted
{
    public $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}