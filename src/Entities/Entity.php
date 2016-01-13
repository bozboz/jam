<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sortable;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Field;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\Node;

class Entity extends Node implements ModelInterface, Sortable
{
	use SanitisesInputTrait;
	use SoftDeletes;
	use DynamicSlugTrait;

	protected $table = 'entities';

	protected $nullable = [];

	protected $fillable = [
		'slug',
		'name',
		'entity_template_id',
	];

	protected $dates = ['deleted_at'];

	protected $values = [];

	static public function boot()
	{
		parent::boot();
		self::saved(function($instance) {
			$instance->updatePath();
		});
	}

	public function sortBy()
	{
		return '_lft';
	}

	public function getValidator()
	{
		return new EntityValidator(
			(array) $this->template->fields()->lists('validation', 'name')->all()
		);
	}

	public function getSlugSourceField()
	{
		return 'name';
	}

	/**
	 * If the slug has changed then softdelete current path for self and all
	 * descendants and insert new path for self and all descendants
	 */
	public function updatePath()
	{
		if ($this->isDirty('slug')) {
			EntityPath::forEntity($this)->delete();
			$this->addPath();
			$this->getDescendants()->map(function($this) {
				$this->addPath();
			});
		}
	}

	/**
	 * Create new EntityPath OR restore old path if already exists
	 */
	public function addPath()
	{
		$this->paths()->withTrashed()->firstOrCreate(['path' => $this->lookupPath()])->restore();
	}

	public function paths()
	{
		return $this->hasMany(EntityPath::class);
	}

	public function lookupPath()
	{
		return $this->getAncestors()->pluck('slug')->push($this->slug)->implode('/');
	}

	public function revisions()
	{
		return $this->hasMany(Revision::class);
	}

	public function latestRevision()
	{
		return $this->revisions()->latest()->first();
	}

	public function publishedRevision()
	{
		return $this->revisions()->whereNotNull('published_at')->latest()->first();
	}

	/**
	 * Load fields values as an array for all fields, not just the values stored
	 * in the db
	 * @param  Revision|null $revision
	 */
	public function loadValues(Revision $revision = null)
	{
		if (is_null($revision)) {
			$revision = $this->latestRevision();
		}

		if ($revision) {
			foreach ($this->template->fields as $field) {
				$field->injectValue($this, $revision, $field->name);
			}
		}
	}

	public function getValue($key)
	{
		return array_key_exists($key, $this->values) ? $this->values[$key] : new Value(compact('key'));
	}

	public function setValue(Value $value)
	{
		$this->values[$value->key] = $value;
		$this->setAttribute($value->key, $value->value);
	}

	public function template()
	{
		return $this->belongsTo(Template::class);
	}

	public function newRevision($input)
	{
		$revision = $this->revisions()->create([]);

		foreach ($this->template->fields as $field) {
			$field->saveValue($revision, $input[$field->getInputName()]);
		}
	}
}
