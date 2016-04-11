<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\Contracts\LinkBuilder as Contract;
use Bozboz\Jam\Entities\Entity;

class LinksDisabled implements Contract
{
    public function updatePaths (Entity $instance)
    {
        // do nothing
    }
}
