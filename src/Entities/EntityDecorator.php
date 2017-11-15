<?php

namespace Bozboz\Jam\Entities;

use Bozboz\Jam\Types\Type;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Admin\Fields\URLField;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Templates\Template;
use Illuminate\Support\Collection as LaravelCollection;
use Bozboz\Admin\Fields\HiddenField;
use Bozboz\Permissions\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Bozboz\Admin\Fields\DateTimeField;
use Bozboz\Admin\Fields\AddonTextField;
use Illuminate\Database\Eloquent\Builder;
use Bozboz\Admin\Base\ModelAdminDecorator;
use Bozboz\Admin\Users\RoleAdminDecorator;
use Bozboz\Admin\Fields\BelongsToManyField;
use Bozboz\Jam\Templates\TemplateDecorator;
use Bozboz\Jam\Entities\Fields\PublishField;
use Bozboz\Admin\Reports\Filters\ArrayListingFilter;
use Bozboz\Admin\Reports\Filters\SearchListingFilter;

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

		$preview = false;

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

			case Revision::PUBLISHED_WITH_DRAFTS:
				$publishedAt = $instance->latestRevision()->created_at->format('d-m-Y H:i');
				$user = $instance->latestRevision()->username;
				$statusLabel = "<small><abbr title='{$publishedAt} by {$user}'>Has Draft</abbr></small>";
				$preview = true;
			break;

			case Revision::EXPIRED:
				$expiredAt = $instance->currentRevision->formatted_expired_at;
				$statusLabel = "<small><abbr title='{$expiredAt}'>Expired</abbr></small>";
			break;

			default:
				$statusLabel = null;
				$preview = true;
			break;
		}

		$path = $instance->canonical_path;

		if ($preview || $instance->deleted_at) {
			$queryParam = $instance->deleted_at ? 'd' : 'p';
			$path = $path ? $path . '?'.$queryParam.'=' . md5(date('ymd')) : null;
			$linkText = 'preview <i class="fa fa-external-link"></i>';
		} else {
			$linkText = '<i class="fa fa-external-link"></i>';
		}

		$columns->prepend(
			'<span title="' . $instance->template->name . '">' . $this->getLabel($instance) . '</span>' . ( $path
				? '&nbsp;&nbsp;<a href="'.url($path).'" target="_blank" title="Go to '.$this->getLabel($instance).'">'.$linkText.'</a>'
				: null
		), 'Name')->put('Status', $statusLabel);

		$columns->put('', $this->getLockState($instance));

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

	protected function getLockState($instance)
	{
		if ( ! $instance->roles->isEmpty()) {
			return '<span title="Restricted to role(s): ' . $instance->roles->implode('name') . '" class="fa fa-lock"></span>';
		}
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
		$canEditStatus = Gate::allows('hide_entity', $instance) || Gate::allows('publish_entity', $instance) || Gate::allows('schedule_entity', $instance);
		$canExpire = Gate::allows('expire_entity', $instance);
		$canRestrictAccess = Gate::allows('gate_entities') && $instance->template->type()->canRestrictAccess();

		$fields = new LaravelCollection(array_filter([
			new TextField('name', ['label' => 'Name *']),
			($instance->exists || request()->old()) && $instance->template->type()->isVisible() ? new AddonTextField('slug', [
				'label' => 'URL *',
				'data-addonText' => $this->getParentUrl($instance),
			]) : null,
			new HiddenField('template_id'),
			new HiddenField('user_id', Auth::id()),
			new HiddenField('parent_id'),
			$canRestrictAccess ? new BelongsToManyField($this->roles, $instance->roles(), [
				'label' => 'Restrict visibility by role',
				'help_text' => 'Leave blank for full public access'
			]) : null,
			$canEditStatus
				? new DateTimeField('currentRevision[published_at]', [
					'label' => 'Published At',
					'placeholder' => date('d/m/Y'),
					'help_text' => "If you enter a date in the future, this will be hidden from the site until that date is reached.",
				])
				: new HiddenField('currentRevision[published_at]'),
			$canExpire
				? new DateTimeField('currentRevision[expired_at]', [
					'label' => 'Expired At',
					'help_text' => "This entity will expire and no longer be visible on the front end when this date is reached.",
				])
				: new HiddenField('currentRevision[expired_at]'),
		]));

		return $fields->merge($this->getTemplateFields($instance))->all();
	}

	protected function getParentUrl($instance)
	{
		$parentUrl = url($instance->parent ? $instance->parent->canonical_path : '').'/';
		if (strlen($parentUrl) > 30) {
			$parentUrl = '<abbr title="' . $parentUrl . '">&hellip;' . substr($parentUrl, -30) . '</abbr>';
		}
		return $parentUrl;
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
			$filters = [
				new ArrayListingFilter(
					'template', $options->prepend('- All -', ''), function($query) {
						$query->whereTemplateId(Input::get('template'));
					}
				),
			];
		} else {
			$filters = [];
		}

		return array_merge($filters, [
			new SearchListingFilter('search', function($builder, $value) {
                $builder->where(function ($query) use ($value) {
                    $query->orWhere('name', 'LIKE', '%' . $value . '%');
                    $this->customSearchFilters($query, $value);
                });
            }),
		]);
	}

	protected function customSearchFilters($query, $value) {}

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
		$query->with(['roles', 'paths', 'currentRevision.user', 'template'])->whereHas('template', function($query) {
			$query->whereTypeAlias($this->type->alias);
		});

		$query->ordered()->with('revisions');
	}

	public function findInstance($id)
	{
		return $this->model->withLatestRevision()->with('currentRevision')->whereId($id)->firstOrFail();
	}

	public function getSyncRelations()
	{
		return ['roles'];
	}
}
