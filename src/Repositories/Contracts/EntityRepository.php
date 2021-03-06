<?php

namespace Bozboz\Jam\Repositories\Contracts;

interface EntityRepository
{
	/**
	 * Get a single entity from a URL path
	 * i.e. "/path/to/entity"
	 * @param  string $path
	 * @return Entity
	 */
	public function getForPath($path);
}