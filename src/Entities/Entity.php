<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Admin\Base\Sorting\NestedSortableTrait;
use Bozboz\Admin\Base\Sorting\Sortable;
use Bozboz\Jam\Contracts\LinkBuilder;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Field;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
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
		return $this->revisions->first();
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
	public function loadAdminValues(Revision $revision = null)
	{
		if ($revision) {
			foreach ($this->template->fields as $field) {
				$field->injectAdminValue($this, $revision);
			}
		}
	}

	/**
	 * Load field values as the frontend wants them as an array for all fields
	 * @param  Revision|null $revision
	 */
	public function loadCurrentValues()
	{
		$this->currentValues->each(function($value) {
			$value->injectValue($this);
		});
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
			switch ($input['status']) {
				case Revision::SCHEDULED:
					$publishedAt = $input['currentRevision']['published_at'];
				break;

				case Revision::PUBLISHED:
					$publishedAt = $this->freshTimestamp();
				break;

				default:
					$publishedAt = null;
			}
			$revision = $this->revisions()->create([
				'published_at' => $publishedAt,
				'user_id' => $input['user_id']
			]);

			foreach ($this->template->fields as $field) {
				$field->saveValue($revision, $input[$field->getInputName()]);
			}

			if ($publishedAt) {
				$this->currentRevision()->associate($revision);
			} else {
				$this->currentRevision()->dissociate();
			}
			$this->save();

			return $revision;
		}
	}

	public function requiresNewRevision($input)
	{
		$latestRevision = $this->latestRevision();

		if ($latestRevision) {
			$templateFields = $this->template->fields->lists('name')->all();

			$currentValues = array_merge(
				array_fill_keys($templateFields, null),
				$latestRevision->fieldValues()->lists('value', 'key')->all()
			);
			$currentValues['status'] = $this->status;
			$currentValues['published_at'] =  $latestRevision->getFormattedPublishedAtAttribute('Y-m-d H:i:s');

			$input['published_at'] = $input['currentRevision']['published_at'];
		}

		return !$latestRevision || count(array_diff_assoc($currentValues, $input)) > 0;
	}
}
