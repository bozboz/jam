<?php

namespace Bozboz\Jam\Contracts;

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