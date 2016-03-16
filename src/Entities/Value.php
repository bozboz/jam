<?php

namespace Bozboz\Jam\Entities;

use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
	protected $table = 'entity_values';

	protected $fillable = [
		'key',
		'value',
		'type_alias',
		'field_id',
	];

	public function revision()
	{
		return $this->belongsTo(Revision::class);
	}

	public function __toString()
	{
		return is_string($this->value) ? $this->value : serialize($this->value);
	}
}
