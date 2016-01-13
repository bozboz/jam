<?php

namespace Bozboz\Entities\Entities;

interface EntityRepositoryInterface
{
	/**
	 * Get a single entity from a URL path
	 * i.e. "/path/to/entity"
	 * @param  string $path
	 * @return Entity
	 */
	public function getForPath($path);
}