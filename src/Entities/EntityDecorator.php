<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Decorators\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\URLField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Fields\FieldMapper;
use Bozboz\Entities\Templates\Template;
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
			$instance->exists() ? new TextField('slug') : null,
			new HiddenField('template_id'),
		]));

		return $fields->merge($instance->template->getFields($this->fieldMapper))->all();
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
}
