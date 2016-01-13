<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\URLField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Templates\TemplateDecorator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
		return [
			'Name' => $this->getLabel($instance),
			// 'URL' => $instance->alias,
			'Type' => $instance->template->alias,
			'Last Revision' => $instance->latestRevision()->created_at->format('d<\s\u\p>S</\s\u\p> M Y, H:i'),
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getFields($instance)
	{
		$fields = new Collection(array_filter([
			new TextField('name'),
			$instance->exists ? new TextField('slug') : null,
			new HiddenField('template_id'),
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

		$instance->loadRealValues($instance->latestRevision());

		foreach($instance->template->fields as $field) {
			$fieldName = $field->name;
			$value = $instance->getValue($fieldName);
			$fields[] = $field->getAdminField($this, $value);
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
		$query->whereHas('template.type', function($query) {
			$query->whereAlias(\Input::get('type'));
		});
	}
}
