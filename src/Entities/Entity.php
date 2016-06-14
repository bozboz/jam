<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Jam\Entities\LinkBuilder;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Field;
use Bozboz\Jam\Mapper;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Kalnoy\Nestedset\Node;

class Entity extends Node implements ModelInterface
{
	use SanitisesInputTrait;
	use SoftDeletes;
	use DynamicSlugTrait;

	protected $table = 'entities';

	protected $nullable = [];

	protected $fillable = [
		'name',
		'slug',
		'parent_id'
	];

	protected static $mapper;

	protected $dates = ['deleted_at'];

	protected $values = [];

	static public function boot()
	{
		parent::boot();
		static::saved([__CLASS__, 'updatePaths']);
	}

	public static function setMapper(Mapper $mapper)
	{
		static::$mapper = $mapper;
	}

	public static function getMapper()
	{
		return static::$mapper;
	}

	public static function updatePaths($entity)
	{
		if ($entity->template) {
			$entity->getMapper()->get($entity->template->type_alias)->updatePaths($entity);
		}
	}

	public function scopeOrdered($query)
	{
		$query->orderBy('name');
	}

	public function scopeOrderByPublishedAt($query)
	{
		$query->select('entities.*')
			->join('entity_revisions as order_join', 'entities.revision_id', '=', 'order_join.id')
			->orderBy('order_join.published_at', 'desc')
			->orderBy('order_join.created_at', 'desc');
	}

	public function isSortable()
	{
		return false;
	}

	public function getValidator()
	{
		$validation = (array) $this->template->fields->map(function($field) {
			return [
				'name' => $field->getInputName(),
				'validation' => $field->validation
			];
		})->all();

		return new EntityValidator(
			array_filter(array_combine(array_column($validation, 'name'), array_column($validation, 'validation')))
		);
	}

	public function getSlugSourceField()
	{
		return 'name';
	}

	public function getForeignKey()
	{
		return 'entity_id';
	}

	public function paths()
	{
		return $this->hasMany(EntityPath::class);
	}

	public function childrenOfType($type)
	{
		return $this->children()->ofType($type);
	}

	public function scopeWithCanonicalPath($query)
	{
		$query->with(['paths' => function($query) {
			$query->whereNull('canonical_id');
		}]);
	}

	public function getCanonicalPathAttribute()
	{
		if ( ! $this->template->type()->isVisible()) {
			return null;
		}

		if (array_key_exists('canonical_path', $this->attributes)) {
			$path = $this->attributes['canonical_path'];
		} elseif ($this->paths->count()) {
			$path = $this->paths->where('canonical_id', null)->pluck('path')->first();
		} else {
			$path = "/{$this->id}/{$this->slug}";
		}

		return $path;
	}

	public function setCanonicalPathAttribute($path)
	{
		$this->attributes['canonical_path'] = $path;
	}

	public function getCanonicalTag()
	{
		if ($this->exists && $this->canonical_path != Request::path()) {
			return '<link rel="canonical" href="'.url($this->canonical_path).'" />';
		}
	}

	/**
	 * Fetch relation to all of this entity's revisions
	 *
	 * @return Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function revisions()
	{
		return $this->hasMany(Revision::class);
	}

	/**
	 * Fetch relation to this entity's current, live revision
	 *
	 * @return Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function currentRevision()
	{
		return $this->belongsTo(Revision::class, 'revision_id');
	}

	/**
	 * Fetch the relation for this entity's latest revision
	 *
	 * @return Illuminate\Database\Eloquent\Relations\Relation
	 */
	public function latestRevision()
	{
		return Revision::whereEntityId($this->id)->latest()->first();
	}

	public function scopeWithLatestRevision($query)
	{
		$query->with(['revisions' => function($query) {
			$query->latest()->limit(1);
		}]);
	}

	public function scopeActive($query)
	{
		$query->whereHas('currentRevision', function($query) {
			$query->isPublished();
		});
	}

	public function scopeOfType($query, $typeAlias)
	{
		$query->whereHas('template', function($query) use ($typeAlias) {
			$query->whereTypeAlias($typeAlias);
		});
	}

	public function scopeOfTemplate($query, $templateAlias, $typeAlias = null)
	{
		$query->whereHas('template', function($query) use ($templateAlias, $typeAlias) {
			if ($typeAlias) {
				$query->whereTypeAlias($typeAlias);
			}
			$query->whereIn('alias', (array)$templateAlias);
		});
	}

	public function scopeJoinValueByKey($query, $key, $alias = 'entity_values')
	{
		$query->join("entity_values as {$alias}", 'entities.revision_id', '=', "{$alias}.revision_id");
		$query->where("{$alias}.key", $key);
	}

	public function scopeWhereValue($query, $key, $value)
	{
		$alias = 'where_value_'.uniqid();
		$query->joinValueByKey($key, $alias)->where("{$alias}.value", $value);
	}

	public function scopeWhereBelongsTo($query, $relation, $related)
	{
		$alias = 'belongs_to_value_'.uniqid();
		$query->joinValueByKey($relation, $alias);
		$query->whereIn("{$alias}.foreign_key", is_object($related) ? (array)$related->getKey() : (array)$related);
	}

	public function scopeWhereBelongsToManyEntity($query, $relation, $related)
	{
		$valueAlias = 'belongs_to_many_value_'.uniqid();
		$entityEntityAlias = 'belongs_to_many_entity_'.uniqid();
		$query->joinValueByKey($relation, $valueAlias);
		$query->join("entity_entity as {$entityEntityAlias}", "{$entityEntityAlias}.value_id", '=', "{$valueAlias}.id");
		$query->whereIn("{$entityEntityAlias}.entity_id", is_object($related) ? (array)$related->getKey() : (array)$related);
	}

	public function currentValues()
	{
		return $this->hasMany(CurrentValue::class, 'revision_id', 'revision_id');
	}

	public function canPublish()
	{
		return !$this->currentRevision || (
			$this->currentRevision
			&& $this->currentRevision->status != Revision::PUBLISHED
		);
	}

	public function canHide()
	{
		return $this->currentRevision;
	}

	public function canSchedule()
	{
		return !$this->currentRevision || (
			$this->currentRevision
			&& $this->currentRevision->status != Revision::SCHEDULED
			&& $this->currentRevision->status != Revision::PUBLISHED
		);
	}

	public function getStatusAttribute()
	{
		if ($this->currentRevision) {
			return $this->currentRevision->status;
		} else {
			return false;
		}
	}

	/**
	 * Load field values as the admin wants them as an array for all fields
	 * @param  Revision|null $revision
	 */
	public function loadAdminValues(Revision $revision)
	{
		if ($revision) {
			foreach ($this->template->fields as $field) {
				$field->injectAdminValue($this, $revision);
			}
		}
	}

	public function getValue($key)
	{
		return array_key_exists($key, $this->values) ? $this->values[$key] : new Value(compact('key'));
	}

	public function template()
	{
		return $this->belongsTo(Template::class);
	}

	/**
	 * Create a new instance of the given model.
	 *
	 * @param  array  $attributes
	 * @param  bool   $exists
	 * @return static
	 */
	public function newInstance($attributes = [], $exists = false)
	{
		if (array_key_exists('type_alias', $attributes)) {
			$mapper = static::getMapper();
			$model = $mapper->get($attributes['type_alias'])->getEntity((array) $attributes);
		} else {
			$model = new static((array) $attributes);
		}
		$model->exists = $exists;

		return $model;
	}
}
