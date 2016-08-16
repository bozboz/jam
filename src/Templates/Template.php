<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Types\Type;
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
		'listing_view',
		'alias',
		'type_alias',
	];

	protected $nullable = [
		'view',
		'listing_view',
	];

	protected function getSlugSourceField()
	{
		return 'name';
	}

	protected function generateUniqueSlug($slug)
	{
		return $slug;
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

	public function entities()
	{
		return $this->hasMany(Entity::class);
	}

	public function type()
	{
		return Entity::getMapper()->get($this->type_alias);
	}
}
