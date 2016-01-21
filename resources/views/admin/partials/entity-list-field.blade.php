<input type="hidden" name="{{ $field->name }}">

@if ($parentEntity->exists)
    @if ($type->templates->count()>1)
        <div class="btn-group">
            <button href="#" class="dropdown-toggle btn btn-sm btn-primary" data-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-plus-square"></i>
                New
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu" role="menu">
                @foreach($type->templates as $template)
                <li>
                    <a class="" type="submit" href="{{ route('admin.entity-list.create-for-list', [$template->alias, $parentEntity->id]) }}">
                        {{ $template->name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    @else
        <a class="btn btn-sm btn-primary" type="submit" href="{{ route('admin.entity-list.create-for-list', [$type->templates->first()->alias, $parentEntity->id]) }}">
            <i class="fa fa-plus-square"></i>
            New
        </a>
    @endif

    <ol class="cards secret-list grid sortable js-entity-list" data-model="{{ $model }}"><!--
    @foreach ($entities as $entity)
     --><li class="grid__item small-12 medium-4 large-3 xlarge-2" data-id="{{ $entity->id }}">
            <div class="cards__item sorting-handle">
                <h4>{{ $entity->name }}</h4>
                <a href="{{ route('admin.entity-list.edit', [$entity->id]) }}" class="cards__btn btn btn-info btn-sm">
                    <i class="fa-pencil fa"></i> Edit
                </a>
                <a class="cards__btn--delete js-delete-entity-btn btn btn-sm btn-danger" type="submit" href="#">
                    <i class="fa-trash fa"></i> Delete
                </a>
            </div>
        </li><!--
    @endforeach
    --></ol>
@endif
