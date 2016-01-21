<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Entities\Contracts\EntityRepository as EntityRepositoryInterface;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityPath;
use Illuminate\Support\Collection;

class EntityRepository implements EntityRepositoryInterface
{
	public function find($id)
	{
		$entity = Entity::active()->whereId($id)->first();

		if (!$entity) {
			return false;
		}

		$entity->setAttribute('canonical', $entity->canonical_path);

		return $entity;
	}

	public function getForPath($path)
	{
		$path = EntityPath::wherePath($path)->first();

		if (!$path) {
			return false;
		}

		$entity = $path->entity()->active()->first();

		if (!$entity) {
			return false;
		}

		$entity->setAttribute('canonical', $path->canonical_path);

		return $entity;
	}

	public function get301ForPath($path)
	{
		$path = EntityPath::wherePath($path)->onlyTrashed()->first();
		if ($path) {
			$redirectPath = EntityPath::whereEntityId($path->entity_id)->whereNull('canonical_id')->first();
			return $redirectPath ? $redirectPath->path : false;
		}
	}
}
