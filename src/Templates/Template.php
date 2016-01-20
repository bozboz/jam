<?php

namespace Bozboz\Entities\Templates;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Fields\Field;
use Bozboz\Entities\Types\Type;
use Kalnoy\Nestedset\Node;

class Template extends Node implements ModelInterface, Sortable
{
	use SanitisesInputTrait;
	use NestedSortableTrait;
	use DynamicSlugTrait;

	protected $table = 'entity_templates';

	protected $fillable = [
		'name',
		'view',
		'type_id'
	];

	protected function getSlugSourceField()
	{
		return 'name';
	}

	/**
	 * Attribute to store the slug in slug.
	 *
	 * @return string
	 */
	protected function getSlugField()
	{
		return 'alias';
	}

	public function sortBy()
	{
		return '_lft';
	}

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
