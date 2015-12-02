<?php

namespace Bozboz\Entities\Entities;

use Bozboz\Admin\Decorators\ModelAdminDecorator;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\URLField;
use Illuminate\Support\Collection;
use Bozboz\Entities\Entities\Entity;

class EntityDecorator extends ModelAdminDecorator
{
	protected $fieldMapper;

	public function __construct(Entity $page, FieldMapper $fieldMapper)
	{
		$this->fieldMapper = $fieldMapper;

		parent::__construct($page);
	}

	public function getColumns($instance)
	{
		return [
			'Name' => $this->getLabel($instance),
			'URL' => $instance->slug,
			'Type' => $instance->template->alias,
		];
	}

	public function getLabel($instance)
	{
		return $instance->name;
	}

	public function getFields($instance)
	{
		$fields = new Collection([
			new TextField('name'),
			new URLField('slug'),
			new HiddenField('template_id'),
		]);

		return $fields->merge($instance->template->getFields($this->fieldMapper))->all();
	}

	/**
	 * Return a new page, associated with given $template
	 *
	 * @param  Bozboz\Entities\Template  $template
	 * @return Bozboz\Entities\Entity
	 */
	public function newEntityOfType(Template $template)
	{
		$page = $this->model->newInstance();

		$page->template()->associate($template);

		return $page;
	}

	/**
	 * Get a new Entity instance, and if a template_id is present in the
	 * attributes, associate it with the Entity.
	 *
	 * @param  array  $attributes
	 * @return Bozboz\Entities\Entity
	 */
	public function newModelInstance($attributes = [])
	{
		if (array_key_exists('template_id', $attributes)) {
			$template = Template::find($attributes['template_id']);
			return $this->newPageOfType($template);
		}

		return $this->model->newInstance();
	}
}
