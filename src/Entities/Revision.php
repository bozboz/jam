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

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function fieldValues()
	{
		return $this->hasMany(Value::class);
	}
}
