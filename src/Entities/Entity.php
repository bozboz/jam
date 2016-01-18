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
	use NestedSortableTrait;

	protected static $linkBuilder;

	protected $table = 'entities';

	protected $nullable = [];

	protected $fillable = [
		'slug',
		'name',
		'entity_template_id',
	];

	protected $dates = ['deleted_at'];

	protected $values = [];

	protected $latestRevision;

	static public function boot()
	{
		parent::boot();
		self::saved([self::$linkBuilder, 'updatePaths']);
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

	public function revisions()
	{
		return $this->hasMany(Revision::class);
	}

	public function latestRevision()
	{
		if (!$this->latestRevision) {
			$this->latestRevison = $this->revisions()->latest()->first();
		}
		return $this->latestRevison;
	}

	public function publishedRevision()
	{
		return $this->revisions()->whereNotNull('published_at')->latest()->first();
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
			$revision = $this->latestRevision();
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
			$revision = $this->revisions()->create([]);

			foreach ($this->template->fields as $field) {
				$field->saveValue($revision, $input[$field->getInputName()]);
			}
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
		}

		return !$latestRevision || count(array_diff_assoc($currentValues, $input)) > 0;
	}
}
