<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Entities\Contracts\LinkBuilder as Contract;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityPath;

class LinkBuilder implements Contract
{
	/**
	 * If the slug has changed then softdelete current path for self and all
	 * descendants and insert new path for self and all descendants
	 * By default only the primary entity types have paths
	 *
	 * @param Bozboz\Entities\Entities\Entity $instance
	 */
	public function updatePaths (Entity $instance)
	{
		if ($this->requiresPath($instance)) {
			EntityPath::forEntity($instance)->delete();
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
		return $instance->template->type->generate_paths
			&& (
				$instance->isDirty('slug')
				||
				$instance->isDirty('parent_id')
			);
	}

	/**
	 * Create new EntityPath OR restore old path if already exists
	 */
	public function addPaths(Entity $instance)
	{
		$path = $this->lookupPath($instance);
		$instance->paths()->withTrashed()->firstOrCreate(['path' => $path])->restore();
	}

	public function lookupPath(Entity $instance)
	{
		return $instance->getAncestors()->pluck('slug')->push($instance->slug)->implode('/');
	}
}
