<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Admin\Fields\URLField;
use Bozboz\Admin\Reports\Filters\ArrayListingFilter;
use Bozboz\Admin\Users\RoleAdminDecorator;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\Fields\PublishField;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Templates\TemplateDecorator;
use Bozboz\Jam\Types\Type;
use Bozboz\Permissions\Facades\Gate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class EntityDecorator extends ModelAdminDecorator
{
	protected $type;
	protected $roles;

	public function __construct(Entity $entity, RoleAdminDecorator $roles)
	{
		$this->roles = $roles;

		parent::__construct($entity);
	}

	public function setType($type)
	{
		$this->type = $type;
		$this->model = $type->getEntity();
	}

	public function getColumns($instance)
	{
		$columns = collect($this->getCustomColumns($instance));

		$columns->prepend($this->getPreviewLink($instance), 'Name');

		return $columns->all();
	}

	protected function getCustomColumns($instance)
	{
		return [];
	}

	protected function getPreviewLink($instance)
	{
		$path = $instance->canonical_path;
		$label = $this->getLabel($instance);

		if ( ! $path) return $label;

		return $label . '&nbsp;&nbsp;<a href="/' . $path . '" target="_blank" title="Go to ' . $label . '"><i class="fa fa-external-link"></i></a>';
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
		$canEditStatus = Gate::allows('hide_entity') || Gate::allows('publish_entity') || Gate::allows('schedule_entity');
		$fields = new Collection(array_filter([
			new TextField('name', ['label' => 'Name *']),
			$instance->exists && $instance->template->type()->isVisible() ? new TextField('slug', ['label' => 'Slug *']) : null,
			new HiddenField('template_id'),
			new HiddenField('user_id', Auth::id()),
			new HiddenField('parent_id'),
			new BelongsToManyField($this->roles, $instance->roles(), [
				'label' => 'Restrict visibility by role',
				'help_text' => 'Leave blank for full public access'
			]),
			$canEditStatus ? new PublishField('status', $instance) : null,
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
	protected function getTemplateFields($instance)
	{
		$fields = [];

		if (Input::has('revision_id')) {
			$revision = Revision::find(Input::get('revision_id'));
		} else {
			$revision = $instance->latestRevision();
		}

		if ($revision) {
			$instance->loadAdminValues($revision);
		}

		foreach($instance->template->fields->sortBy('sorting') as $field) {
			$fieldName = $field->name;
			$value = $instance->getValue($fieldName);
			$fields[] = $field->getAdminField($instance, $this, $value);
		}

		return $fields;
	}

	public function getListingFilters()
	{
		$options = Template::whereTypeAlias($this->type->alias)
			->orderBy('name')->get()
			->pluck('name', 'id');

		if ($options->count() > 1) {
			return [
				new ArrayListingFilter(
					'template', $options->prepend('- All -', ''), function($query) {
						$query->whereTemplateId(Input::get('template'));
					}
				),
			];
		} else {
			return [];
		}
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
		$query->with(['paths', 'currentRevision.user', 'template'])->whereHas('template', function($query) {
			$query->whereTypeAlias($this->type->alias);
		});

		$query->ordered();
	}

	public function findInstance($id)
	{
		return $this->model->withLatestRevision()->whereId($id)->firstOrFail();
	}

	public function getSyncRelations()
	{
		return ['roles'];
	}
}
