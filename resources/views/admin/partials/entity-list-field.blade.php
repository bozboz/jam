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
    @else
        <a class="btn btn-sm btn-primary" type="submit" href="{{ route('admin.entity-list.create-for-list', [$templates->first()->type_alias, $templates->first()->alias, $parentEntity->id]) }}">
            <i class="fa fa-plus"></i>
            New
        </a>
    @endif

    <ol class="cards secret-list grid js-sortable js-entity-list" data-model="{{ $model }}"><!--
    @foreach ($entities as $entity)
     --><li class="js-nested-item grid__item small-12 medium-4 large-3 xlarge-2" data-id="{{ $entity->id }}">
            <div class="cards__item sorting-handle js-sorting-handle">
                <h4>{{ $entity->name }}</h4>
                @if (!$entity->status)
                    <div class="panel panel-danger"><div class="panel-heading">
                        Hidden
                    </div></div>
                @elseif ($entity->currentRevision->published_at->isFuture())
                    <div class="panel panel-warning"><div class="panel-heading">
                        <abbr title="{{ $entity->currentRevision->formatted_published_at }}">Scheduled</abbr>
                    </div></div>
                @endif
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
