<?php

namespace Bozboz\Jam\Repositories\Contracts;

use Bozboz\Jam\Entities\Entity;

interface LinkBuilder
{
	public function updatePaths(Entity $instance);
}