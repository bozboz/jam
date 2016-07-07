<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\Field as AdminField;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Admin\Fields\TextField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\SortableEntity;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class EntityList extends Field
{
    private $parentEntity = null;

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new EntityListField($instance, $this, $this->newListQuery($instance)->with('template', 'template.fields', 'currentRevision')->get(), [
            'name' => $this->getInputName(),
            'label' => $this->getInputLabel()
        ]);
    }

    public function getOptionFields()
    {
        return [
            new SelectField('options_array[type]', [
                'label' => 'Type',
                'options' => app('EntityMapper')->getAll()->map(function($type) {
                    return $type->name;
                })->prepend('- Please Select -')
            ]),
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        $repository = app()->make(\Bozboz\Jam\Repositories\Contracts\EntityRepository::class);
        $entity->setAttribute(
            $value->key,
            $this->newListQuery($entity)->active()->withFields()->get()->each(function($entity) {
                $entity->injectValues();
            })
        );
        return $value;
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $entity->setAttribute($value->key, $value);
        return $value;
    }

    protected function newListQuery($entity)
    {
        return $entity->childrenOfType($this->getOption('type'))->defaultOrder();
    }
}

class EntityListField extends AdminField
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
        return view('jam::admin.partials.entity-list-field', [
            'templates' => Template::whereTypeAlias($this->field->getOption('type'))->get(),
            'entities' => $this->entityList,
            'field' => $this->field,
            'parentEntity' => $this->parentEntity,
            'model' => SortableEntity::class,
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
