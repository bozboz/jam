<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Base\Model;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Types\Type;

class Template extends Model
{
	protected $table = 'entity_templates';

	protected $fillable = [
		'name',
		'alias',
		'type_id'
	];

	public function getValidator()
	{
		return new TemplateValidator;
	}

	public function fields()
	{
		return $this->hasMany(Field::class);
	}

	public function entity()
	{
		return $this->belongsTo(Entity::class);
	}

	public function type()
	{
		return $this->belongsTo(Type::class);
	}
}
