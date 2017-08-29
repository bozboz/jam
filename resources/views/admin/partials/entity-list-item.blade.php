<li class="js-nested-item grid__item small-12 medium-4 large-3 xlarge-2" data-id="{{ $entity->id }}">
    <div class="cards__item sorting-handle js-sorting-handle">
        <h4>{{ $entity->name }}</h4>
        @if ( ! $entity->status)
            <div class="panel panel-danger"><div class="panel-heading">
                Hidden
            </div></div>
        @elseif ($entity->status === Bozboz\Jam\Entities\Revision::PUBLISHED_WITH_DRAFTS)
            <div class="panel panel-warning"><div class="panel-heading">
                Has Draft
            </div></div>
        @elseif ($entity->status === Bozboz\Jam\Entities\Revision::SCHEDULED)
            <div class="panel panel-warning"><div class="panel-heading">
                <abbr title="{{ $entity->currentRevision->formatted_published_at }}">Scheduled</abbr>
            </div></div>
        @elseif ($entity->status === Bozboz\Jam\Entities\Revision::EXPIRED)
            <div class="panel panel-danger"><div class="panel-heading">
                <abbr title="{{ $entity->currentRevision->formatted_expired_at }}">Expired</abbr>
            </div></div>
        @endif

        <div class="cards__btn">
            <a href="{{ route('admin.entity-list.edit', [$entity->id]) }}" class="btn btn-info btn-sm">
                <i class="fa-pencil fa"></i> Edit
            </a>

            @if ($entity->status === Bozboz\Jam\Entities\Revision::EXPIRED)
                <a href="{{ route('admin.entities.publish', [$entity->id]) }}" class="btn btn-success btn-sm js-publish-entity-btn">
                    <i class="fa-save fa"></i> Publish
                </a>
            @endif
        </div>

        <a class="cards__btn--delete js-delete-entity-btn btn btn-sm btn-danger" type="submit" href="#">
            <i class="fa-trash fa"></i> Delete
        </a>
    </div>
</li>
