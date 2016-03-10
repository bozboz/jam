<?php

namespace Bozboz\Jam\Fields\Options;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
	protected $table = 'entity_template_field_options';

	protected $fillable = [
		'field_id',
		'key',
		'value',
	];
}
