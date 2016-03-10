<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\URLField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\Fields\PublishField;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Templates\TemplateDecorator;
use Bozboz\Entities\Types\Type;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class EntityDecorator extends ModelAdminDecorator
{
	protected $fieldMapper;

	public function __construct(Entity $entity, FieldMapper $fieldMapper)
	{
		$this->fieldMapper = $fieldMapper;

		parent::__construct($entity);
	}

	public function getColumns($instance)
	{
		switch ($instance->status) {
			case Revision::PUBLISHED:
				$publishedAt = $instance->currentRevision->published_at->format('d M Y H:i');
				$statusLabel = "<small><abbr title='{$publishedAt}'>Published</abbr></small>";
			break;

			case Revision::SCHEDULED:
				$publishedAt = $instance->currentRevision->published_at->format('d M Y H:i');
				$statusLabel = "<small><abbr title='{$publishedAt}'>Scheduled</abbr></small>";
			break;

			default:
				$statusLabel = null;
			break;
		}
		return [
			'Name' => $this->getLabel($instance),
			'URL' => $instance->template->type->generate_paths ? link_to($instance->canonical_path, route('entity', array($instance->canonical_path), false)) : null,
			'Status' => $statusLabel,
		];
	}


	public function getHeading($plural = false)
	{
		$type = Type::whereAlias(Input::get('type'))->pluck('name');
		$name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $type);
		return $plural ? str_plural($name) : $name;
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getFields($instance)
	{
		$fields = new Collection(array_filter([
			new TextField('name', ['label' => 'Name *']),
			$instance->exists && $instance->template->type->visible ? new TextField('slug', ['label' => 'Slug *']) : null,
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
	 * @param  Bozboz\Entities\Entity  $instance
	 * @return array
	 */
	public function getTemplateFields($instance)
	{
		$fields = [];

		$instance->loadRealValues();

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
	 * @param  Bozboz\Entities\Templates\Template  $template
	 * @return Bozboz\Entities\Entities\Entity
	 */
	public function newEntityOfType(Template $template)
	{
		$entity = $this->model->newInstance();

		$entity->template()->associate($template);

		return $entity;
	}

	/**
	 * Get a new Entity instance, and if a template_id is present in the
	 * attributes, associate it with the Entity.
	 *
	 * @param  array  $attributes
	 * @return Bozboz\Entities\Entities\Entity
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
		$query->with('template')->whereHas('template.type', function($query) {
			$query->whereAlias(\Input::get('type'));
		})->orderBy($this->model->sortBy());
	}
}
