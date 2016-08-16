<?php

namespace Bozboz\Jam\Fields\Options;

use Bozboz\Jam\Fields\Field;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
	protected $table = 'entity_template_field_options';

	protected $fillable = [
		'field_id',
		'key',
		'value',
	];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
