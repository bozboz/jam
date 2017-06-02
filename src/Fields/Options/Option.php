<?php

namespace Bozboz\Jam\Fields\Options;

use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Revisionable;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use Revisionable;

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

    public function template()
    {
        return $this->field->template();
    }
}
