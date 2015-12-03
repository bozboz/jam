<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Models\BaseInterface;
use Bozboz\Admin\Traits\DynamicSlugTrait;
use Bozboz\Admin\Traits\SanitisesInputTrait;
use Bozboz\Entities\Field;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\Node;

class Entity extends Node implements BaseInterface
{
	protected $table = 'entities';

	protected $nullable = [];

	protected $fillable = [
		'slug',
		'name',
		'entity_template_id',
	];

	protected $dates = ['deleted_at'];

	protected $fields = [];

	use SanitisesInputTrait;
	use SoftDeletes;
	use DynamicSlugTrait;

	public function getValidator()
	{
		\Debugbar::info($this->template->fields()->lists('validation', 'name'));
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

		$templateFields = $this->template->fields()->lists('name');
		$fieldValues = $revision->fieldValues()->lists('value', 'key');

		$this->fields = array_merge(array_fill_keys($templateFields, null), $fieldValues);
	}

	public function getValue($key = null)
	{
		if (!$this->fields) {
			$this->loadValues();
		}

		if ($key) {
			return $this->fields[$key];
		} else {
			return $this->fields;
		}
	}

	public function template()
	{
		return $this->belongsTo(Template::class);
	}

	public function newRevision($input)
	{
		$revision = $this->revisions()->create([]);

		$fieldValues = [];

		foreach ($this->template->fields as $field) {
			$fieldValues[] = [
				'field_id' => $field->id,
				'key' => $field->pivot->name,
				'value' => $input[e($field->pivot->name)],
			];
		}

		$revision->fieldValues()->createMany($fieldValues);
	}
}
