<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Models\Base;

class Revision extends Base
{
	protected $table = 'entity_revisions';

	protected $fillable = [
		'entity_id',
		'published_at'
	];

	public function getValidator()
	{

	}

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function fieldValues()
	{
		return $this->hasMany(Value::class);
	}
}
