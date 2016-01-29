<?php

namespace Bozboz\Entities\Entities;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
	protected $table = 'entity_revisions';

	protected $fillable = [
		'entity_id',
		'published_at'
	];

	protected $dates = ['published_at'];

	const UNPUBLISHED = 0;
	const PUBLISHED = 1;
	const SCHEDULED = 2;

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function fieldValues()
	{
		return $this->hasMany(Value::class);
	}

	public function scopeIsPublished($query)
	{
		$query->where('published_at', '<', $this->freshTimestamp());
	}

	public function getStatusAttribute()
	{
		if (is_null($this->published_at)) {
			return static::UNPUBLISHED;
		} elseif ($this->published_at->timestamp > time()) {
			return static::SCHEDULED;
		} else {
			return static::PUBLISHED;
		}
	}

	public function getFormattedPublishedAtAttribute()
	{
		return $this->published_at->format('d-m-Y H:i');
	}
}
