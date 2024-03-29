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
use Bozboz\Jam\Types\EntityList as Type;

class EntityList extends Field
{
    private $parentEntity = null;

    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new EntityListField($instance, $this, $this->newListQuery($instance)->with('template', 'template.fields', 'currentRevision')->get(), [
            'tab' => $this->getTab(),
            'name' => $this->getInputName(),
            'label' => $this->getInputLabel(),
            'help_text_title' => $this->help_text_title,
            'help_text' => $this->help_text,
        ]);
    }

    public function getOptionFields()
    {
        return [
            new SelectField('options_array[type]', [
                'label' => 'Type',
                'options' => app('EntityMapper')->getAll(Type::class)->map(function($type) {
                    return $type->name;
                })->prepend('- Please Select -')
            ]),
        ];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        $entity->setAttribute(
            $value->key,
            $this->newListQuery($entity)->active()->withFields()->get()
        );
        return $value;
    }

    public function injectAdminValue(Entity $entity, Revision $revision)
    {
        $value = parent::injectAdminValue($entity, $revision);
        $entity->setAttribute($value->key, $value);
        return $value;
    }

    public function injectDiffValue(Entity $entity, Revision $revision)
    {
        $value = $revision->fieldValues->where('key', $this->name)->first() ?: new Value(['key' => $this->name]);
        $this->injectValue($entity, $value);
        $entity->setAttribute(
            $value->key,
            $entity->getAttribute($value->key)->map(function($entity) {
                return $entity->name;
            })->implode("\n")
        );
        return $value;
    }

    protected function newListQuery($entity)
    {
        return $entity->childrenOfType($this->getOption('type'))->defaultOrder();
    }

    public function duplicateValue(Value $oldValue, Value $newValue)
    {
        if ($oldValue->revision->entity->id === $newValue->revision->entity->id) {
            parent::duplicateValue($oldValue, $newValue);
        } else {
            $newParent = $newValue->revision->entity;
            $oldValue->templateField->newListQuery($oldValue->revision->entity)->get()->each(function($entity) use ($newParent) {
                $newEntity = $entity->replicate();
                $newEntity->appendTo($newParent);
                $newEntity->save();

                $newRevision = $entity->latestRevision()->duplicate($newEntity);
                $newEntity->currentRevision()->associate($newRevision);
                $newEntity->save();
            });
        }
    }
}

class EntityListField extends AdminField
{
    protected $parentEntity;
    protected $field;
    protected $entityList;
    protected $view = 'admin::fields.helptext-before-field';

    public function __construct(Entity $parentEntity, $field, $entityList, array $attributes = [])
    {
        parent::__construct($attributes);

        $this->parentEntity = $parentEntity;
        $this->field = $field;
        $this->entityList = $entityList;
    }

    public function getInput()
    {
        $templates = Template::whereTypeAlias($this->field->getOption('type'))
            ->where(function($query) {
                $query->whereHas('entities', function($query) {
                    $query->selectRaw('COUNT(*) as count');
                    $query->havingRaw('entity_templates.max_uses > count');
                    $query->orHavingRaw('entity_templates.max_uses IS NULL');
                });
                $query->orWhere(function($query) {
                    $query->doesntHave('entities');
                });
            })
            ->orderBy('name')
            ->get();

        return view('jam::admin.partials.entity-list-field', [
            'templates' => $templates,
            'entities' => $this->entityList,
            'field' => $this->field,
            'parentEntity' => $this->parentEntity,
            'model' => SortableEntity::class,
        ])->render();
    }

    public function getJavascript()
    {
        $token = csrf_token();
        $tokenField = csrf_field();

        $deleteRoute = route('admin.entities.destroy', ['--id--']);
        $deleteMethodField = method_field('DELETE');

        $publishRoute = route('admin.entities.publish', ['--id--']);

        return <<<JAVASCRIPT
            jQuery(function($){

                var jsDeleteEntityForm = $('<form>')
                    .prop('action', '{$deleteRoute}')
                    .prop('method', 'POST')
                    .append('{$tokenField}')
                    .append('{$deleteMethodField}');

                $('.js-delete-entity-{$this->name}').click(function(e) {
                    e.preventDefault();

                    var entityId = $(this).closest('[data-id]').data('id');

                    if (confirm('Are you sure you want to delete?')) {
                        $('body').append(
                            jsDeleteEntityForm.prop(
                                'action',
                                jsDeleteEntityForm.prop('action').replace('--id--', entityId)
                            )
                        );

                        jsDeleteEntityForm.submit();
                    }
                });

                $('.js-publish-entity-{$this->name}').click(function(e) {
                    e.preventDefault();

                    var entityId = $(this).closest('[data-id]').data('id'),
                        route = '${publishRoute}',
                        uri = route.replace('--id--', entityId);

                    $.ajax({
                        type: 'POST',
                        url: uri,
                        headers: {
                            'X-CSRF-TOKEN': '${token}'
                        },
                        complete: function() {
                            window.location.reload()
                        }
                    });
                });

            });
JAVASCRIPT;
    }
}
