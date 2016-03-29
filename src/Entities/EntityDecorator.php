<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\URLField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Fields\PublishField;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Templates\TemplateDecorator;
use Bozboz\Jam\Types\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class EntityDecorator extends ModelAdminDecorator
{
	protected $type;

	public function __construct(Entity $entity)
	{
		parent::__construct($entity);
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getColumns($instance)
	{
		switch ($instance->status) {
			case Revision::PUBLISHED:
				$publishedAt = $instance->currentRevision->formatted_published_at;
				$user = $instance->currentRevision->username;
				$statusLabel = "<small><abbr title='{$publishedAt} by {$user}'>Published</abbr></small>";
			break;

			case Revision::SCHEDULED:
				$publishedAt = $instance->currentRevision->formatted_published_at;
				$user = $instance->currentRevision->username;
				$statusLabel = "<small><abbr title='{$publishedAt} by {$user}'>Scheduled</abbr></small>";
			break;

			default:
				$statusLabel = null;
			break;
		}
		$path = $instance->canonical_path;
		return [
			'Name' => $this->getLabel($instance)
				. ( $path
					? '&nbsp;&nbsp;<a href="/'.$path.'" target="_blank" title="Go to '.$this->getLabel($instance).'"><i class="fa fa-external-link"></i></a>'
					: null
			),
			'Status' => $statusLabel,
		];
	}

	public function isSortable()
	{
		return $this->type->getSorter()->isSortable();
	}

	public function getHeading($plural = false)
	{
		return $this->type->getHeading($plural);
	}

	public function getHeadingForInstance($instance)
	{
		return $instance->template()->value('name');
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getFields($instance)
	{
		$fields = new Collection(array_filter([
			new TextField('name', ['label' => 'Name *']),
			$instance->exists && $instance->template->type()->isVisible() ? new TextField('slug', ['label' => 'Slug *']) : null,
			new HiddenField('template_id'),
			new HiddenField('user_id', Auth::id()),
			new PublishField('status', $instance),
		]));

		return $fields->merge($this->getTemplateFields($instance))->all();
	}

	/**
	 * Iterate over a template's fields, and build an array of field instances
	 * found in the FieldMapper lookup.
	 *
	 * @param  Bozboz\Jam\Entity  $instance
	 * @return array
	 */
	public function getTemplateFields($instance)
	{
		$fields = [];

		if (Input::has('revision_id')) {
			$revision = Revision::find(Input::get('revision_id'));
		} else {
			$revision = $instance->latestRevision();
		}
		$instance->loadAdminValues($revision);

		foreach($instance->template->fields->sortBy('sorting') as $field) {
			$fieldName = $field->name;
			$value = $instance->getValue($fieldName);
			$fields[] = $field->getAdminField($instance, $this, $value);
		}

		return $fields;
	}

	/**
	 * Return a new entity, associated with given $template
	 *
	 * @param  Bozboz\Jam\Templates\Template  $template
	 * @return Bozboz\Jam\Entities\Entity
	 */
	public function newEntityOfType(Template $template)
	{
		$entity = $this->model->newInstance(['type_alias' => $template->type_alias]);

		$entity->template()->associate($template);

		return $entity;
	}

	/**
	 * Get a new Entity instance, and if a template_id is present in the
	 * attributes, associate it with the Entity.
	 *
	 * @param  array  $attributes
	 * @return Bozboz\Jam\Entities\Entity
	 */
	public function newModelInstance($attributes = [])
	{
		if (array_key_exists('template_id', $attributes)) {
			$template = Template::find($attributes['template_id']);
			return $this->newEntityOfType($template);
		}

		return $this->model->newInstance();
	}

	protected function modifyListingQuery(Builder $query)
	{
		$query->with('template')->whereHas('template', function($query) {
			$query->whereTypeAlias(\Input::get('type'));
		});

		$this->type->getSorter()->sortQuery($query);
	}

	public function findInstance($id)
	{
		return $this->model->withLatestRevision()->whereId($id)->firstOrFail();
	}
}
