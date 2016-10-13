<?php

namespace Bozboz\Jam\Templates;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\Model;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Fields\Field;
use Bozboz\Jam\Fields\Text;
use Bozboz\Jam\Types\Type;

class Template extends Model
{
	use DynamicSlugTrait;

	protected $table = 'entity_templates';

	protected $fillable = [
		'name',
		'view',
		'listing_view',
		'alias',
		'type_alias',
		'max_uses',
	];

	protected $nullable = [
		'view',
		'listing_view',
		'max_uses',
	];

	static public function boot()
	{
		static::created(function($template) {
			if ($template->type()->isVisible()) {
				$template->fields()->create([
					'type_alias' => 'text',
					'name' => 'meta_title',
					'validation' => 'required'
				]);
				$template->fields()->create([
					'type_alias' => 'text',
					'name' => 'meta_description',
				]);
			}
		});
	}

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
		return 'name';
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
