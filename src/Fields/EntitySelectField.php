<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Entities\Entity;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class EntitySelectField extends TemplateSelectField
{
    protected function getFields()
    {
        return array_merge(parent::getFields(), [
            new SelectField('options_array[entity]', [
                'label' => 'Entity',
                'options' => ['' => '- All -']+Entity::pluck('name', 'id')->toArray(),
                'class' => 'js-entity-select form-control select2'
            ]),
        ]);
    }

    public function getJavascript()
    {
        $templates = Template::with('entities')->get()->map(function($template) {
            $entities = $template->entities->sortBy('name')->pluck('name', 'id');
            return [
                'id' => $template->id,
                'entities' => $entities
            ];
        })->toArray();
        $entities = json_encode(array_combine(array_column($templates, 'id'), array_column($templates, 'entities')));
        return parent::getJavascript() . <<<JAVASCRIPT
            jQuery(function($) {
                var entities = {$entities};
                $('.js-entity-type-select').change(function() {
                    if ($(this).val()) {
                        var templates = $('.js-entity-template-select').data('templates')[$(this).val()];
                        var options = {};
                        for(var i in templates) {
                            $.extend(options, entities[i]);
                        }
                        updateEntitySelect(options);
                    }
                });
                $('.js-entity-template-select').change(function() {
                    if ($(this).val()) {
                        updateEntitySelect(entities[$(this).val()]);
                    }
                });
                setTimeout(function() {
                    updateEntitySelect(entities[$('.js-entity-template-select').val()]);
                }, 20);
                function updateEntitySelect(options) {
                    var t = $('.js-entity-select');

                    var selected = t.val();

                    t.children(':not(:first)').remove();

                    for(var i in options || {}) {
                        t.append(
                            $('<option>').val(i).html(options[i])
                        );
                    }

                    t.val(selected);
                }
            });
JAVASCRIPT;
    }
}