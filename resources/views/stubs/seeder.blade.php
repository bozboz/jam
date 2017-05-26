
        if (Template::whereTypeAlias('{{$template->type_alias}}')
            ->whereAlias('{{$template->alias}}')
            ->with('fields.options')
            ->exists()) {
            throw new \Exception('Template already exists! type: {{$template->type_alias}}, template: {{$template->alias}}');
        }

        /**
         * Create template
         * type: {{$template->type_alias}}, template: {{$template->alias}}
         */
        $template = Template::create([
            'name' => '{{$template->name}}',
            'view' => '{{$template->view}}',
            'listing_view' => '{{$template->listing_view}}',
            'alias' => '{{$template->alias}}',
            'type_alias' => '{{$template->type_alias}}',
            'max_uses' => {{$template->max_uses ?: 'NULL'}},
        ]);

        @foreach ($template->fields->filter(function($field) {
            switch ($field->name) {
                case 'meta_title':
                case 'meta_description':
                    return false;
                default:
                    return true;
            }
        }) as $field)

            /**
             * Create field
             * name: {{ $field->name }}
             */
            $field = Field::create([
                'template_id' => $template->id,

                'name' => '{{ $field->name }}',
                'type_alias' => '{{ $field->type_alias }}',
                'validation' => {!! $field->validation ? "'{$field->validation}'" : 'NULL' !!},
                'help_text_title' => {!! $field->help_text_title ? "'{$field->help_text_title}'" : 'NULL' !!},
                'help_text' => {!! $field->help_text ? "'{$field->help_text}'" : 'NULL' !!},
                'sorting' => {{$field->sorting}},
            ]);

            /**
             * Create field options
             */
            @foreach ($field->options as $option)

                Option::create([
                    'field_id' => $field->id,

                    'key' => '{{$option->key}}',
                    'value' => '{{$option->value}}',
                ]);
            @endforeach

        @endforeach
