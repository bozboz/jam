<?php

namespace Bozboz\JamBlog\Fields;

use Bozboz\Admin\Fields\FieldGroup;
use Bozboz\Jam\Templates\Template;
use Bozboz\Jam\Types\Type;

class TemplateSelectField extends FieldGroup
{
    public function __construct($name)
    {
        parent::__construct($name, [
            new SelectField('options_array['.$name.'_type]', [
                'label' => 'Type',
                'options' => ['' => '- All -']+Type::lists('name', 'id')->toArray(),
                'class' => 'js-entity-type-select form-control select2'
            ]),
            new SelectField('options_array['.$name.'_template]', [
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