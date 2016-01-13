<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Entities\Entities\EntityRepositoryInterface;
use Illuminate\Support\Collection;

class EntityRepository implements EntityRepositoryInterface
{
	public function getForPath($path)
	{
		$entity = Entity::with('template', 'template.fields')->whereHas('paths', function($query) use ($path) {
			$query->where('path', $path);
		})->first();
		return $entity;
	}
}
