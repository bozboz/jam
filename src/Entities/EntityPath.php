<?php

namespace Bozboz\Jam\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntityPath extends Model
{
	use SoftDeletes;

	protected $guarded = [
		'id',
	];

	public function entity()
	{
		$relation = $this->belongsTo(Entity::class);

		if (config('jam.preview-mode')) {
			$relation->withTrashed();
		}

		return $relation;
	}

	public function scopeForEntity($query, Entity $entity)
	{
		$query->where(function($query) use ($entity) {
			$query->whereEntityId($entity->id)->orWhereHas('entity', function($query) use ($entity) {
				$query->whereDescendantOf($entity, 'and')->withTrashed();
			});
		});
	}

	public function canonical()
	{
		return $this->belongsTo(self::class, 'canonical_id');
	}

	public function getCanonicalPathAttribute()
	{
		if ($this->canonical_id) {
			return $this->canonical->path;
		}
	}
}
