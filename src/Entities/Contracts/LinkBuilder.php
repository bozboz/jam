<?php

namespace Bozboz\Jam\Entities\Contracts;

use Bozboz\Jam\Entities\Entity;

interface LinkBuilder
{
	public function updatePaths(Entity $instance);
}