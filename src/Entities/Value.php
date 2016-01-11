<?php

namespace Bozboz\Entities\Entities;

use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
	protected $table = 'entity_values';

	protected $fillable = [
		'page_revision_id',
		'key',
		'value'
	];

	public function pageRevision()
	{
		return $this->belongsTo(Revision::class);
	}

	public function __toString()
	{
		return is_string($this->value) ? $this->value : serialize($this->value);
	}
}
