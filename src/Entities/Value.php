<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Fields\Field;
use Illuminate\Database\Eloquent\Model;

class Value extends Model
{
	protected $table = 'entity_values';

	protected $fillable = [
		'key',
		'value',
		'foreign_key',
		'type_alias',
		'field_id',
	];

	public function revision()
	{
		return $this->belongsTo(Revision::class);
	}

	public function templateField()
	{
		return $this->belongsTo(Field::class, 'field_id');
	}

	public function getForeignKey()
	{
		return 'value_id';
	}

	public function duplicate($revision)
	{
		$newValue = $this->replicate();
		$newValue->revision()->associate($revision);
		$newValue->save();

		$field = (new Field)->newInstance(['type_alias' => $this->type_alias]);
		$field->duplicateValue($this, $newValue);

		return $newValue;
	}

	public function __toString()
	{
		return is_string($this->value) ? $this->value : serialize($this->value);
	}
}
