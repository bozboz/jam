<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Contracts\EntityRepository as EntityRepositoryInterface;
use Bozboz\Jam\Entities\CurrentValue;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityPath;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\Collection;

class EntityRepository implements EntityRepositoryInterface
{
	protected $mapper;

	function __construct()
	{
		$this->mapper = app()->make('EntityMapper');
	}

	public function find($id)
	{
		$entity = Entity::active()->whereId($id)->first();

		if (!$entity) {
			return false;
		}

		$entity->setAttribute('canonical', $entity->canonical_path);

		return $entity;
	}

	public function forType($typeAlias)
	{
		$entities = (new $this->mapper->get($typeAlias))->all();
	}

	public function getForPath($path)
	{
		$path = EntityPath::wherePath(trim($path, '/'))->first();

		if (!$path) {
			return false;
		}

		$entity = $path->entity()->active()->first();

		if (!$entity) {
			return false;
		}

		$entity->setAttribute('canonical', $path->canonical_path);

		return $entity;
	}

	public function get301ForPath($path)
	{
		$path = EntityPath::wherePath($path)->onlyTrashed()->first();
		if ($path) {
			$redirectPath = EntityPath::whereEntityId($path->entity_id)->whereNull('canonical_id')->first();
			return $redirectPath ? $redirectPath->path : false;
		}
	}

	public function hydrate(Entity $entity)
	{
		$entity->setAttribute('breadcrumbs', $this->breadcrumbs($entity));
		$entity->setAttribute('child_pages', $this->childPages($entity));
		$this->loadCurrentValues($entity);
	}

	public function breadcrumbs(Entity $entity)
	{
		return $entity->ancestors()->active()->get()->push($entity)->map(function($crumb) {
			return (object) [
				'url' => $crumb->canonical_path,
				'label' => $crumb->name
			];
		});
	}

	public function childPages(Entity $entity, $fields = [])
	{
		$children = $entity->children()->with('template')->active()->get();
		return $this->loadCurrentListingValues($children);
	}

	public function loadCurrentListingValues($entities)
	{
		if ($entities) {
			$listingFields = array_filter(array_unique(explode(',', $entities->map(function($entity) {
				return $entity->template->listing_fields;
			})->implode(','))));

			if ($listingFields) {
				return $this->loadCurrentValues($entities, $listingFields);
			} else {
				return $entities;
			}
		}
	}

	public function loadCurrentValues($entities, $fields = ['*'])
	{
		if ($entities instanceof Entity) {
			$entityCollection = collect([$entities]);
		} else {
			$entityCollection = $entities;
		}

		$revisionIds = $entityCollection->map(function($entity) {
			return $entity->revision_id;
		});

		if (!count($revisionIds)) {
			return $entities;
		}

		$query = DB::table('entity_values as ev')->select(
				'ev.revision_id',
				'ev.id as value_id',
				'ev.key',
				'ev.value',
				'ev.foreign_key',
				'ev.type_alias',
				'etfo.key as option_key',
				'etfo.value as option_value'
			)
			->leftJoin('entity_template_field_options as etfo', 'ev.field_id', '=', 'etfo.field_id')
			->whereIn('ev.revision_id', $revisionIds);

		if ($fields[0] != '*') {
			$query->whereIn('ev.key', $fields);
		}

		$results = $query->get();

		$values = [];
		foreach ($results as $row) {
			$values[$row->revision_id][$row->value_id]['key'] = $row->key;
			$values[$row->revision_id][$row->value_id]['value'] = $row->value;
			$values[$row->revision_id][$row->value_id]['type_alias'] = $row->type_alias;
			if ($row->option_key) {
				$values[$row->revision_id][$row->value_id]['options'][$row->option_key] = $row->option_value;
			}
		}

		foreach($values as $revisionId => $revisionValues) {
			$entity = $entityCollection->where('revision_id', $revisionId)->first();
			foreach ($revisionValues as $valueId => $valueAttributes) {
				$value = new CurrentValue($valueAttributes);
				$value->injectValue($entity);
			}
		}

		return $entities;
	}

	public function newRevision(Entity $entity, $input)
	{
		if ($this->requiresNewRevision($entity, $input)) {
			switch ($input['status']) {
				case Revision::SCHEDULED:
					$publishedAt = $input['currentRevision']['published_at'];
				break;

				case Revision::PUBLISHED:
					$publishedAt = !$input['currentRevision']['published_at'] || (new Carbon($input['currentRevision']['published_at']))->isFuture()
						? $entity->freshTimestamp()
						: $input['currentRevision']['published_at'];
				break;

				default:
					$publishedAt = null;
			}
			$revision = $entity->revisions()->create([
				'published_at' => $publishedAt,
				'user_id' => $input['user_id']
			]);

			foreach ($entity->template->fields as $field) {
				$field->saveValue($revision, $input[$field->getInputName()]);
			}

			if ($publishedAt) {
				$entity->currentRevision()->associate($revision);
			} else {
				$entity->currentRevision()->dissociate();
			}
			$entity->save();

			return $revision;
		}
	}

	public function requiresNewRevision(Entity $entity, $input)
	{
		$latestRevision = $entity->latestRevision();

		if ($latestRevision) {
			$templateFields = $entity->template->fields->lists('name')->all();

			$currentValues = array_merge(
				array_fill_keys($templateFields, null),
				$latestRevision->fieldValues()->lists('value', 'key')->all()
			);
			$currentValues['status'] = $entity->status;
			$currentValues['published_at'] =  $latestRevision->getFormattedPublishedAtAttribute('Y-m-d H:i:s');

			$input['published_at'] = $input['currentRevision']['published_at'];
		}
		return !$latestRevision || count($this->diffValues($currentValues, $input)) > 0;
	}

	protected function diffValues($array1, $array2)
	{
		foreach($array1 as $key => $value)
		{
			if(is_array($value))
			{
				  if(!isset($array2[$key]))
				  {
					  $difference[$key] = $value;
				  }
				  elseif(!is_array($array2[$key]))
				  {
					  $difference[$key] = $value;
				  }
				  else
				  {
					  $new_diff = $this->diffValues($value, $array2[$key]);
					  if($new_diff != FALSE)
					  {
							$difference[$key] = $new_diff;
					  }
				  }
			  }
			  elseif(!isset($array2[$key]) || $array2[$key] != $value)
			  {
				  $difference[$key] = $value;
			  }
		}
		return !isset($difference) ? 0 : $difference;
	}
}
