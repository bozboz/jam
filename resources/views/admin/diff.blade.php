@extends('admin::layouts.default')

@section('main')
    <dl>
    @foreach ($previousEntity->getAttributes() as $key => $data)
        <dt>{{ $key }}</dt>
        <dd style="word-wrap:break-word;" data-from="{{ $data }}" data-to="{{ $entity->getAttribute($key) }}"></dd>
    @endforeach
    </dl>

    <script src="http://kpdecker.github.io/jsdiff/diff.js"></script>
    <script>
    var entityData = document.querySelectorAll('[data-from]');

    for (var elem of entityData) {

        var one = elem.getAttribute('data-from'),
            other = elem.getAttribute('data-to'),
            color = '',
            span = null;
        var diff = JsDiff.diffLines(one, other),
            display = elem,
            fragment = document.createDocumentFragment();
        diff.forEach(function(part){
          // green for additions, red for deletions
          // grey for common parts
          color = part.added ? 'green' :
            part.removed ? 'red' : 'grey';
          span = document.createElement('span');
          span.style.color = color;
          span.appendChild(document
            .createTextNode(part.value));
          fragment.appendChild(span);
        });
        display.appendChild(fragment);

    }

    </script>
    <style type="text/css">
        [data-from] span {
            display: block;
        }
    </style>
@stop