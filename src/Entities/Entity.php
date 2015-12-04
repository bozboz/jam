<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Models\BaseInterface;
use Bozboz\Admin\Models\Media;
use Bozboz\Admin\Traits\DynamicSlugTrait;
use Bozboz\Admin\Traits\SanitisesInputTrait;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Field;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\Node;
use Illuminate\Support\Collection;

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

	public $fields = [];
	public $values = [];

	use SanitisesInputTrait;
	use SoftDeletes;
	use DynamicSlugTrait;

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

		$templateFields = $this->template->fields()->lists('name')->toArray();
		$this->fillable = array_merge($this->fillable, $templateFields);
		$fieldKeys = array_fill_keys($templateFields, null);

		$fieldValues = $revision->fieldValues;
		foreach ($fieldValues as $value) {
			$this->values[$value->key] = $value;
			$this->attributes[$value->key] = $value->value;
		}
	}

	public function getValue($key = null)
	{
		return $this->values[$key];
	}

	public function getValues()
	{
		return $this->values;
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
			$fieldValue = [
				'field_id' => $field->id,
				'key' => $field->name,
			];
			switch ($field->type_alias) {
				case 'gallery':
					$fieldValue['value'] = null;
					$value = $revision->fieldValues()->create($fieldValue);
					$data = @array_filter($input[e($field->name).'_relationship']);
					$value->gallery()->sync(is_array($data) ? $data : []);
					break;

				default:
					$fieldValue['value'] = $input[e($field->name)];
					$revision->fieldValues()->create($fieldValue);
					break;
			}
		}
	}
}
