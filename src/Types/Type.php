<?php

namespace Bozboz\Jam\Types;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Media\MediableTrait;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Templates\Template;

class Type extends Model
{
	use DynamicSlugTrait;
	use MediableTrait;

	protected $table = 'entity_types';

	protected $fillable = [
		'name',
		'alias',
		'visible',
		'generate_paths',
	];

	public function getSlugSourceField()
	{
		return 'name';
	}

	public function getSlugField()
	{
		return 'alias';
	}

	public function entities()
	{
		return $this->hasManyThrough(Entity::class, Template::class);
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
