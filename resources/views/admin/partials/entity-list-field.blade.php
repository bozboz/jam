<input type="hidden" name="{{ $field->name }}" value="{{ $parentEntity->id }}">

@if ($parentEntity->exists)
    @if ($templates->count()>1)
        <div class="btn-group">
            <button href="#" class="dropdown-toggle btn btn-sm btn-primary" data-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-plus"></i>
                New
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu" role="menu">
                @foreach($templates as $template)
                <li>
                    <a class="" type="submit" href="{{ route('admin.entity-list.create-for-list', [$template->type_alias, $template->alias, $parentEntity->id]) }}">
                        {{ $template->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    @elseif ($templates->count())
        <a class="btn btn-sm btn-primary" type="submit" href="{{ route('admin.entity-list.create-for-list', [$templates->first()->type_alias, $templates->first()->alias, $parentEntity->id]) }}">
            <i class="fa fa-plus"></i>
            New
        </a>
    @endif

    <ol class="cards secret-list grid js-sortable js-entity-list" data-model="{{ $model }}"><!--
    @foreach ($entities as $entity)
     -->@include($entity->template->listing_view ?: 'jam::admin.partials.entity-list-item')<!--
    @endforeach
    --></ol>
@else
    <p>You must save as draft or publish before you can begin adding {{ str_plural(str_replace('_', ' ', $field->name)) }}.</p>
@endif
