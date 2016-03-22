<?php

namespace Bozboz\Jam\Contracts;

use Bozboz\Jam\Entities\Entity;

interface LinkBuilder
{
	public function updatePaths(Entity $instance);
}