<?php

namespace Bozboz\Entities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntityPath extends Model
{
	use SoftDeletes;

	protected $fillable = [
		'entity_id',
		'path',
	];

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function scopeForEntity($query, Entity $entity)
	{
		$query->where(function($query) use ($entity) {
			$query->whereEntityId($entity->id)->orWhereHas('entity', function($query) use ($entity) {
				$query->whereDescendantOf($entity, 'and');
			});
		});
	}

	public function canonical()
	{
		return $this->belongsTo(self::class, 'canonical_id');
	}

	public function getCanonicalPathAttribute()
	{
		return $this->canonical ? $this->canonical->path : null;
	}
}