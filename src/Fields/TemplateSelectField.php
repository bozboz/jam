<?php

namespace Bozboz\Jam\Fields;

use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Admin\Fields\SelectField;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class TemplateSelectField extends FieldGroup
{
    public function __construct($name)
    {
        parent::__construct($name, $this->getFields());
    }

    protected function getFields()
    {
        return [
            new SelectField('options_array[type]', [
                'label' => 'Type',
                'options' => app('EntityMapper')->getAll()->map(function($type) {
                    return ($type->menu_title ?: 'Content') . ' - ' . $type->name;
                })->sort()->prepend('- All -', ''),
                'class' => 'js-entity-type-select form-control select2'
            ]),
            new SelectField('options_array[template]', [
                'label' => 'Template',
                'options' => ['' => '- All -']+Template::lists('name', 'id')->toArray(),
                'class' => 'js-entity-template-select form-control select2'
            ]),
        ];
    }

    public function getJavascript()
    {
        $types = app('EntityMapper')->getAll()->map(function($type) {
            $templates = $type->templates()->pluck('name', 'id');
            return [
                'id' => $type->id,
                'templates' => $templates
            ];
        })->toArray();
        $templates = json_encode(array_combine(array_keys($types), array_column($types, 'templates')));
        return <<<JAVASCRIPT
            jQuery(function($) {
                var templates = {$templates};
                $('.js-entity-template-select').data('templates', templates);
                $('.js-entity-type-select').change(function() {
                    if ($(this).val()) {
                        updateTemplateSelect(templates[$(this).val()]);
                    }
                });
                updateTemplateSelect(templates[$('.js-entity-type-select').val()]);

                function updateTemplateSelect(options) {
                    var t = $('.js-entity-template-select');

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