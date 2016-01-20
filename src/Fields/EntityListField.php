<?php

namespace Bozboz\Entities\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\Field as AdminField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Entities\Entities\Entity;
use Bozboz\Entities\Entities\EntityDecorator;
use Bozboz\Entities\Entities\Revision;
use Bozboz\Entities\Entities\Value;
use Bozboz\Entities\Templates\Template;
use Bozboz\Entities\Types\Type;

class EntityListField extends Field
{
	public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
	{
		return new EntityListInput($instance, $this, $this->getValue($value), ['name' => $this->name]);
	}

	public function getOptionFields()
	{
		return [
			new SelectField('options_array[type]', [
				'label' => 'Type',
				'options' => Type::lists('name', 'alias')->prepend('- Please Select -')
			]),
		];
	}

	public function injectValue(Entity $entity, Revision $revision, $realValue)
	{
		$value = parent::injectValue($entity, $revision, $realValue);

		if (!$realValue) {
			$entity->setAttribute($value->key, $this->getValue($value));
		}
	}

	public function getValue(Value $value)
	{
		return Entity::with('template.type', 'revisions')->whereHas('template.type', function ($query) {
			$query->whereAlias($this->getOption('type'));
		})->defaultOrder()->get()->transform(function ($entity) {
			$entity->loadValues();
			return $entity;
		});
	}
}

class EntityListInput extends AdminField
{
	protected $parentEntity;
	protected $field;
	protected $entityList;

	public function __construct(Entity $parentEntity, $field, $entityList, array $attributes = [])
	{
		parent::__construct($attributes);

		$this->parentEntity = $parentEntity;
		$this->field = $field;
		$this->entityList = $entityList;
	}

	public function getInput()
	{
		return view('entities::admin.partials.entity-list-field', [
			'type' => Type::with('templates')->whereAlias($this->field->getOption('type'))->first(),
			'entities' => $this->entityList,
			'field' => $this->field,
			'parentEntity' => $this->parentEntity,
			'model' => Entity::class,
		])->render();
	}

	public function getJavascript()
	{
		$route = route('admin.entities.destroy', ['--id--']);
		$token = csrf_field();
		$method = method_field('DELETE');
		return <<<JAVASCRIPT
			jQuery(function($){

				var jsDeleteEntityForm = $('<form>')
					.prop('action', '{$route}')
					.prop('method', 'POST')
					.append('{$token}')
					.append('{$method}');

				$('.js-delete-entity-btn').click(function(e){
					e.preventDefault();
					if (confirm('Are you sure you want to delete?')) {
						var entityId = $(this).closest('[data-id]').data('id');
						$('body').append(
							jsDeleteEntityForm.prop(
								'action',
								jsDeleteEntityForm.prop('action').replace('--id--', entityId)
							)
						);
						jsDeleteEntityForm.submit();
					}
				});

			});
JAVASCRIPT;
	}
}