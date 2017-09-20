<?php

namespace Bozboz\Jam\Entities;

use Illuminate\Support\Facades\App;
use Bozboz\Admin\Exceptions\ValidationException;
use Bozboz\Jam\Entities\Contracts\LinkBuilder as Contract;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\MessageBag;

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
		$this->deletePaths($instance);

		if ( ! $instance->trashed()) {
			$this->addPaths($instance);
			$instance->descendants()->active()->get()->map(function($instance) {
				$instance->template->type()->updatePaths($instance);
			});
		}
	}

	/**
	 * Create new EntityPath OR restore old path if already exists
	 */
	public function addPaths(Entity $instance)
	{
		try {
			$paths = $this->calculatePathsForInstance($instance);

			$canonicalPathString = $paths->shift();
			$canonicalPath = $this->createOrRestorePath($canonicalPathString, $instance);
			$canonicalId = $canonicalPath->id;

			$paths->each(function($path) use ($instance, $canonicalId) {
				$this->createOrRestorePath($path, $instance, $canonicalId);
			});
		} catch (QueryException $e) {
			$message = 'There is already a page with the url ' . url(str_replace_array('\?', $e->getBindings(), '?'));
			if (App::runningInConsole()) {
				throw new UrlGenerationException($message);
			} else {
				throw new ValidationException(new MessageBag([
					'slug' => $message
				]));
			}
		}
	}

	public function createOrRestorePath($pathString, $instance, $canonicalId = null)
	{
		EntityPath::onlyTrashed()->where('entity_id', '<>', $instance->id)->wherePath($pathString)->forceDelete();
		$path = $instance->paths()->withTrashed()->firstOrCreate(['path' => $pathString, 'canonical_id' => $canonicalId]);
		$path->restore();

		return $path;
	}

	protected function calculatePathsForInstance(Entity $instance)
	{
		return collect(
			str_pad(trim($instance->getAncestors()->pluck('slug')->push($instance->slug)->implode('/'), '/'), 1, '/')
		);
	}

	/**
	 * Delete existing EntityPath instances for an entity
	 */
	public function deletePaths(Entity $instance)
	{
		EntityPath::forEntity($instance)->delete();
	}
}
