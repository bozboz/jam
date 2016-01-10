<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Base\DynamicSlugTrait;
use Bozboz\Admin\Base\ModelInterface;
use Bozboz\Admin\Base\SanitisesInputTrait;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Field;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Kalnoy\Nestedset\Node;

class Entity extends Node implements ModelInterface
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

	public function getValidator()
	{
		return new EntityValidator(
			(array) $this->template->fields()->lists('validation', 'name')->toArray()
		);
	}

	public function getSlugSourceField()
	{
		return 'name';
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

		$templateFields = $this->template->fields;
		$templateFields->map(function($field) {
			array_push($this->fillable, $field->name);
		});

		if ($revision) {
			foreach ($templateFields as $field) {
				$field->injectValue($this, $revision, $field->name);
			}
		}
	}

	public function getValue($key)
	{
		return array_key_exists($key, $this->values) ? $this->values[$key] : new Value(compact('key'));
	}

	public function setValue($key, $value)
	{
		$this->values[$key] = $value;
	}

	public function template()
	{
		return $this->belongsTo(Template::class);
	}

	public function newRevision($input)
	{
		$revision = $this->revisions()->create([]);

		foreach ($this->template->fields as $field) {
			$field->saveValue($revision, $input[$field->getName()]);
		}
	}
}
