<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Entities\Contracts\LinkBuilder as Contract;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;

class LinkBuilder implements Contract
{
	public function isVisible()
	{
		return true;
	}

	/**
	 * If the slug has changed then softdelete current path for self and all
	 * descendants and insert new path for self and all descendants
	 * By default only the primary entity types have paths
	 *
	 * @param Bozboz\Jam\Entities\Entity $instance
	 */
	public function updatePaths (Entity $instance)
	{
		if ($this->requiresPath($instance)) {
			$this->deletePaths($instance);
			$this->addPaths($instance);
			$instance->getDescendants()->map(function($instance) {
				$this->addPaths($instance);
			});
		}
	}

	/**
	 * Discern whether or not an entity needs to generate new paths
	 *
	 * @param  Entity $instance
	 * @return boolean
	 */
	protected function requiresPath(Entity $instance)
	{
		return $instance->isDirty('slug')
				||
				$instance->isDirty('parent_id');
	}

	/**
	 * Create new EntityPath OR restore old path if already exists
	 */
	public function addPaths(Entity $instance)
	{
		$path = $this->calculatePathForInstance($instance);
		EntityPath::onlyTrashed()->where('entity_id', '<>', $instance->id)->wherePath($path)->forceDelete();
		$instance->paths()->withTrashed()->firstOrCreate(['path' => $path])->restore();
	}

	protected function calculatePathForInstance(Entity $instance)
	{
		return str_pad(trim($instance->getAncestors()->pluck('slug')->push($instance->slug)->implode('/'), '/'), 1, '/');
	}

	/**
	 * Delete existing EntityPath instances for an entity
	 */
	public function deletePaths(Entity $instance)
	{
		EntityPath::forEntity($instance)->delete();
	}
}
