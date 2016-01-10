<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Media\MediableTrait;
use Bozboz\Entities\Entity;
use Bozboz\Entities\Templates\Template;

class Type extends Model
{
	protected $table = 'entity_types';

	protected $fillable = [
		'name',
		'alias'
	];

	use MediableTrait;

	public function entities()
	{
		return $this->hasMany(Entity::class);
	}

	public function templates()
	{
		return $this->hasMany(Template::class);
	}

	public function getValidator()
	{
		return new TypeValidator;
	}
}
