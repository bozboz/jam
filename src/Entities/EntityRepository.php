<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Contracts\EntityRepository as EntityRepositoryInterface;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;

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
		$path = EntityPath::wherePath(trim($path, '/'))->first();

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

	public function hydrate(Entity $entity)
	{
		$entity->setAttribute('breadcrumbs', $this->breadcrumbs($entity));
		$entity->setAttribute('child_pages', $this->childPages($entity));
		$entity->loadValues();
	}

	public function breadcrumbs(Entity $entity)
	{
		return $entity->ancestors()->active()->get()->push($entity)->map(function($crumb) {
			return (object) [
				'url' => $crumb->canonical_path,
				'label' => $crumb->name
			];
		});
	}

	public function childPages(Entity $entity)
	{
		return $entity->children()->active()->get();
	}
}
