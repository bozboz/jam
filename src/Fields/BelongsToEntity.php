<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\BelongsToField;
use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Entities\EntityDecorator;
use Bozboz\Jam\Entities\Revision;
use Bozboz\Jam\Entities\Value;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class BelongsToEntity extends Field
{
    public function getAdminField(Entity $instance, EntityDecorator $decorator, Value $value)
    {
        return new BelongsToField($decorator, $this->getValue($value), [
                'name' => $this->getInputName(),
                'label' => $this->getInputLabel()
            ],
            function($query) {
                if (property_exists($this->options_array, 'template')) {
                    $query->whereHas('template', function($query) {
                        $query->whereId($this->options_array->template);
                    });
                } elseif (property_exists($this->options_array, 'type')) {
                    $query->whereHas('template.type', function($query) {
                        $query->whereId($this->options_array->type);
                    });
                }
            }
        );
    }

    public function getOptionFields()
    {
        return [new TypeSelect];
    }

    public function injectValue(Entity $entity, Value $value)
    {
        parent::injectValue($entity, $value);
        $relation = $this->getValue($value)->first();
        $repository = app()->make(\Bozboz\Jam\Contracts\EntityRepository::class);
        $repository->loadCurrentListingValues($relation);
        $entity->setAttribute($value->key, $relation);
    }

    public function getValue(Value $value)
    {
        return $value->belongsTo(Entity::class, 'foreign_key');
    }

    public function saveValue(Revision $revision, $value)
    {
        $valueObj = parent::saveValue($revision, $value);
        $valueObj->foreign_key = $value;
        $valueObj->save();
        return $valueObj;
    }
}

class TypeSelect extends FieldGroup
{

    public function __construct()
    {
        parent::__construct('Entity', [
            new SelectField('options_array[type]', [
                'label' => 'Type',
                'options' => ['' => '- All -']+Type::lists('name', 'id')->toArray(),
                'class' => 'js-entity-type-select form-control select2'
            ]),
            new SelectField('options_array[template]', [
                'label' => 'Template',
                'options' => ['' => '- All -']+Template::lists('name', 'id')->toArray(),
                'class' => 'js-entity-template-select form-control select2'
            ]),
        ]);
    }

    public function getJavascript()
    {
        $types = Type::with('templates')->get()->map(function($type) {
            $templates = $type->templates->pluck('name', 'id');
            return [
                'id' => $type->id,
                'templates' => $templates
            ];
        })->toArray();
        $types = json_encode(array_combine(array_column($types, 'id'), array_column($types, 'templates')));
        return <<<JAVASCRIPT
            jQuery(function($) {
                var types = {$types};
                $('.js-entity-type-select').change(function() {
                    if ($(this).val()) {
                        updateTemplateSelect(types[$(this).val()]);
                    }
                });
                updateTemplateSelect(types[$('.js-entity-type-select').val()]);

                function updateTemplateSelect(options) {
                    var t = $('.js-entity-template-select');

                    t.children(':not(:first)').remove();

                    for(var i in options || {}) {
                        t.append(
                            $('<option>').val(i).html(options[i])
                        );
                    }
                }
            });
JAVASCRIPT;
    }
}