<?php

namespace Bozboz\Jam\Repositories\Criteria;

use Bozboz\Jam\Repositories\Contracts\EntityRepository;

abstract class Criteria {

    /**
     * @param $entity
     * @param EntityRepository $repository
     * @return mixed
     */
    public abstract function apply($entity, EntityRepository $repository);
}