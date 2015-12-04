<?php

namespace Bozboz\Entities\Types;

use Bozboz\Admin\Models\Base;
use Bozboz\Admin\Traits\MediableTrait;
use Bozboz\Entities\Entity;
use Bozboz\Entities\Templates\Template;

class Type extends Base
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
