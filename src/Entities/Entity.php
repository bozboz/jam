<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Jam\Entities\Events\EntityDeleted;
use Bozboz\Jam\Entities\Events\EntitySaved;
use Bozboz\Jam\Entities\LinkBuilder;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Field;
use Bozboz\Jam\Mapper;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Bozboz\Permissions\Facades\Gate;
use Illuminate\Database\Eloquent\SoftDeletes;
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

		static::deleted(function($entity) {
			static::$dispatcher->fire(new EntityDeleted($entity));
		});
	}

	public static function setMapper(Mapper $mapper)
	{
		static::$mapper = $mapper;
	}

	public static function getMapper()
	{
		return static::$mapper;
	}

	public function sortBy()
	{
		return 'name';
	}

	public function scopeOrdered($query)
	{
		$query->orderBy('name');
	}

	public function scopeOrderByPublishedAt($query)
	{
		$query->select('entities.*')
			->leftJoin('entity_revisions as current_revision_join', 'entities.revision_id', '=', 'current_revision_join.id')
			->join('entity_revisions as latest_revision_join', function($join) {
				$join->on('entities.id', '=', 'latest_revision_join.entity_id');
			})
			->groupBy('latest_revision_join.entity_id')
			->orderByRaw('coalesce(current_revision_join.published_at, latest_revision_join.created_at) desc');
	}

	public function isSortable()
	{
		return false;
	}

	public function getValidator()
	{
		if (str_contains(Request::get('submit'), '"status":"draft"')) {
			$validation = [];
		} else {
			$validation = $this->getValidationRulesFromTemplate();
		}

		return new EntityValidator(
			array_filter(array_combine(array_column($validation, 'name'), array_column($validation, 'validation')))
		);
	}

	protected function getValidationRulesFromTemplate()
	{
		return (array) $this->template->fields->map(function($field) {
			return [
				'name' => $field->getInputName(),
				'validation' => $field->validation
			];
		})->all();
	}

	public function getSlugSourceField()
	{
		return 'name';
	}

	protected function generateUniqueSlug($slug)
	{
		return $slug;
	}

	public function getForeignKey()
	{
		return 'entity_id';
	}

	public function roles()
	{
		return $this->belongsToMany(\Bozboz\Admin\Users\Role::class, 'entity_role')->withTimestamps();
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
		$query->with(['template', 'paths' => function($query) {
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
		if ($this->exists && $this->canonical_path) {
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
		return $this->hasMany(Revision::class)->latest();
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
		return $this->revisions->first();
	}

	public function scopeWithLatestRevision($query)
	{
		$query->with(['revisions' => function($query) {
			$query->limit(1);
		}]);
	}

	public function scopeActive($query)
	{
		if ( ! config('jam.preview-mode')) {
			$query->whereHas('currentRevision', function($query) {
				$query->isPublished();
			});
		}
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
		$query->select('entities.*');
		$query->join("entity_values as {$alias}", 'entities.revision_id', '=', "{$alias}.revision_id");
		$query->where("{$alias}.key", $key);
	}

	public function scopeWhereValue($query, $key, $operator, $value = null)
	{
        if (func_num_args() == 2) {
            list($value, $operator) = [$operator, '='];
        }
		$alias = 'where_value_'.uniqid();
		$query->joinValueByKey($key, $alias)->where("{$alias}.value", $operator, $value);
	}

	public function scopeWhereBelongsTo($query, $relation, $related)
	{
		$alias = 'belongs_to_value_'.uniqid();
		$query->joinValueByKey($relation, $alias);
		$query->whereIn("{$alias}.foreign_key", is_object($related) ? (array)$related->getKey() : (array)$related);
	}

	public function scopeWhereBelongsToManyEntity($query, $relation, $related)
	{
		$query->whereBelongsToMany($relation, $related, 'entity_entity', 'entity_id');
	}

	public function scopeWhereBelongsToMany($query, $relation, $related, $pivot, $foreignKey)
	{
		$valueAlias = 'belongs_to_many_value_'.uniqid();
		$entityEntityAlias = 'belongs_to_many_entity_'.uniqid();
		$query->joinValueByKey($relation, $valueAlias);
		$query->join("{$pivot} as {$entityEntityAlias}", "{$entityEntityAlias}.value_id", '=', "{$valueAlias}.id");
		$query->whereIn("{$entityEntityAlias}.{$foreignKey}", is_object($related) ? (array)$related->getKey() : (array)$related);
	}

	public function scopeWhereNotBelongsToManyEntity($query, $relation, $related)
	{
		$query->whereNotBelongsToMany($relation, $related, 'entity_entity', 'entity_id');
	}

	public function scopeWhereNotBelongsToMany($query, $relation, $related, $pivot, $foreignKey)
	{
		$valueAlias = 'belongs_to_many_value_'.uniqid();
		$entityEntityAlias = 'belongs_to_many_entity_'.uniqid();
		$query->joinValueByKey($relation, $valueAlias);
		$query->join("{$pivot} as {$entityEntityAlias}", "{$entityEntityAlias}.value_id", '=', "{$valueAlias}.id");
		$query->whereNotIn("{$entityEntityAlias}.{$foreignKey}", is_object($related) ? (array)$related->getKey() : (array)$related);
	}

	public function currentValues()
	{
		if (config('jam.preview-mode')) {
			// This isn't an especially good way of going about things... needs revisiting
			$latestRevisions = Revision::groupBy(\DB::raw('entity_id DESC'))->orderBy('created_at', 'DESC')->pluck('id');
			return $this->hasManyThrough(CurrentValue::class, Revision::class)->whereIn('entity_revisions.id', $latestRevisions);
		} else {
			return $this->hasMany(CurrentValue::class, 'revision_id', 'revision_id');
		}
	}

	public function canPublish()
	{
		return !$this->currentRevision || (
			$this->status !== Revision::PUBLISHED
			|| $this->status === Revision::PUBLISHED_WITH_DRAFTS
		);
	}

	public function canHide()
	{
		return $this->currentRevision;
	}

	public function canSchedule()
	{
		return !$this->currentRevision || (
			$this->status !== Revision::SCHEDULED
			&& $this->status !== Revision::PUBLISHED
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

	public function getPublishedAtAttribute($format = null)
	{
		if ($this->currentRevision) {
			return $format ? $this->currentRevision->published_at->format($format) : $this->currentRevision->published_at;
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

	public function setValue($key, $value)
	{
		$this->values[$key] = $value;
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

    public function newCollection(array $models = array())
    {
        $collection = new Collection($models);
        if ( ! $collection->isEmpty() && $collection->first()->relationLoaded('currentValues')) {
            $collection->injectValues();
        }
        return $collection;
    }

	/**
	 * Create a new model instance that is existing.
	 *
	 * @param  array  $attributes
	 * @param  string|null  $connection
	 * @return static
	 */
	public function newFromBuilder($attributes = [], $connection = null)
	{
		$attributes = (array) $attributes;

		$newInstanceAttributes = [];
		if (array_key_exists('type_alias', $attributes)) {
			$template = Template::find($attributes['template_id']);
			$newInstanceAttributes['type_alias'] = $template->type_alias;
		}

		$model = $this->newInstance($newInstanceAttributes, true);

		$model->setRawAttributes($attributes, true);

		$model->setConnection($connection ?: $this->connection);

		return $model;
	}

	public function scopeWithFields($builder, $fields = ['*'])
	{
		if (is_string($fields)) {
			$fields = array_slice(func_get_args(), 1);
		}
		$builder->with(['currentValues' => function($query) use ($fields) {
			$query
				->selectFields($fields)
				->with('dynamicRelation');
		}]);
	}

    public function loadFields($fields = ['*'])
    {
        if (is_string($fields)) {
            $fields = func_get_args();
        }

        $query = $this->newQuery()->withFields($fields);

        $query->eagerLoadRelations([$this]);

        return $this->injectValues();
    }

	public function injectValues()
	{
		$this->currentValues->each(function($value) {
			$value->injectValue($this);
		});

		return $this;
	}

	public function scopeAuthorised($query)
	{
		if ( ! Gate::allows('view_gated_entities')) {
			$query->wheredoesntHave('roles');

			if (\Auth::check()) {
				$query->orWhereHas('roles', function($q) {
					$q->where('roles.id', \Auth::user()->role_id);
				});
			}
		}
	}

	public function scopeOnlyAuthorisedTo($query, \Bozboz\Permissions\UserInterface $user)
	{
		if (Gate::allows('view_gated_entities')) {
			$query->has('roles');
		} else {
			$query->whereHas('roles', function($q) use ($user) {
				$q->where('roles.id', $user->role_id);
			});
		}
	}
}