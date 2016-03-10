<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;
use Bozboz\Entities\Contracts\LinkBuilder;
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
	use NestedSortableTrait {
		sort as traitSort;
	}

	protected static $linkBuilder;

	protected $table = 'entities';

	protected $nullable = [];

	protected $fillable = [
		'name',
		'slug',
	];

	protected $dates = ['deleted_at'];

	protected $values = [];

	protected $latestRevision;

	static public function boot()
	{
		parent::boot();
		static::saved([static::$linkBuilder, 'updatePaths']);
	}

    public static function setLinkBuilder(LinkBuilder $linkBuilder)
    {
        static::$linkBuilder = $linkBuilder;
    }

    public static function getLinkBuilder()
    {
        return static::$linkBuilder;
    }

	public function sortBy()
	{
		return '_lft';
	}

	public function sort($before, $after, $parent)
	{
		$this->traitSort($before, $after, $parent);
		static::$linkBuilder->updatePaths($this);
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

	public function paths()
	{
		return $this->hasMany(EntityPath::class);
	}

	public function getCanonicalPathAttribute()
	{
		if ($this->paths->count()) {
			$path = $this->paths->where('canonical_id', null)->pluck('path')->first();
		} elseif ($this->template->type->visible) {
			$path = "/{$this->id}/{$this->slug}";
		} else {
			$path = null;
		}

		return $path;
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
		if (!$this->latestRevision) {
			$this->latestRevison = $this->revisions()->latest()->with('fieldValues')->first();
		}
		return $this->latestRevison;
	}

	public function scopeActive($query)
	{
		$query->whereHas('currentRevision', function($query) {
			$query->isPublished();
		});
	}

	public function canPublish()
	{
		return $this->currentRevision->status != Revision::PUBLISHED;
	}

	public function canHide()
	{
		return $this->currentRevision->status != Revision::UNPUBLISHED;
	}

	public function canSchedule()
	{
		return $this->currentRevision->status != Revision::SCHEDULED && $this->currentRevision->status != Revision::PUBLISHED;
	}

	public function getStatusAttribute()
	{
		if ($this->currentRevision) {
			return $this->currentRevision->status;
		}
	}

	/**
	 * Load values and inject them in to the entity
	 * @param  bool $realValues true: inject actual db values,
	 *                          false: inject values as you'd use them, i.e. relations, etc...
	 * @param  Revision|null $revision Defaults to latestRevision
	 */
	protected function _loadValues($realValues, Revision $revision = null)
	{
		if (is_null($revision)) {
			$revision = $this->revision;
		}

		if ($revision) {
			foreach ($this->template->fields as $field) {
				$field->injectValue($this, $revision, $realValues);
			}
		}
	}

	/**
	 * Load field values as the admin wants them as an array for all fields
	 * @param  Revision|null $revision
	 */
	public function loadRealValues(Revision $revision = null)
	{
		$this->_loadValues(true, $revision);
	}

	/**
	 * Load field values as the frontend wants them as an array for all fields
	 * @param  Revision|null $revision
	 */
	public function loadValues(Revision $revision = null)
	{
		$this->_loadValues(false, $revision);
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
		if ($this->requiresNewRevision($input)) {
			if ($input['status'] == Revision::SCHEDULED) {
				$publishedAt = $input['currentRevision']['published_at'];
			} elseif ($input['status'] == Revision::PUBLISHED) {
				$publishedAt = $this->freshTimestamp();
			} else {
				$publishedAt = null;
			}
			$revision = $this->revisions()->create([
				'published_at' => $publishedAt,
				'user_id' => $input['user_id']
			]);

			foreach ($this->template->fields as $field) {
				$field->saveValue($revision, $input[$field->getInputName()]);
			}
			return $revision;
		}
	}

	public function requiresNewRevision($input)
	{
		$currentRevision = $this->currentRevision;

		if ($currentRevision) {
			$templateFields = $this->template->fields->lists('name')->all();

			$currentValues = array_merge(
				array_fill_keys($templateFields, null),
				$currentRevision->fieldValues()->lists('value', 'key')->all()
			);
			$currentValues['status'] = $currentRevision->status;
			$currentValues['published_at'] = $currentRevision->published_at->format('Y-m-d H:i:s');

			$input['published_at'] = $input['currentRevision']['published_at'];
		}

		return !$currentRevision || count(array_diff_assoc($currentValues, $input)) > 0;
	}
}
