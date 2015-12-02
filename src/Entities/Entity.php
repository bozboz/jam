<?php

namespace Bozboz\Entities\Entities;

use Baum\Node;
use Bozboz\Admin\Models\BaseInterface;
use Bozboz\Admin\Traits\SanitisesInputTrait;
use Bozboz\Entities\Field;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

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
	use SoftDeletingTrait;

	public function getValidator()
	{
		return $this->template->getValidator();
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

	public function type()
	{
		return $this->belongsTo(Type::class);
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

		$templateFields = $this->template->fields()->lists('pivot.name');
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
				'value' => $input[$field->pivot->name],
			];
		}

		$revision->fieldValues()->createMany($fieldValues);
	}
}
