<?php

namespace Bozboz\Entities\Entities;

use Baum\Extensions\Eloquent\Model;

class Value extends Model
{
	protected $table = 'entity_field_values';

	protected $fillable = [
		'page_revision_id',
		'field_id',
		'key',
		'value'
	];

	public function pageRevision()
	{
		return $this->belongsTo(Revision::class);
	}

	public function field()
	{
		return $this->belongsTo(Field::class);
	}
}
