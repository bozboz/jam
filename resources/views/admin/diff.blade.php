@extends('admin::layouts.default')

@section('main')
    <div class="form-row discrete">
        <a class="btn-default pull-right space-left btn btn-sm" href="{{ url()->previous() }}">
            <i class="fa fa-list-alt"></i>
            Back
        </a>
    </div>
    <h1>Diff for {{ $entity->name }}</h1>
    <p style="text-align:center">Additions are shown in <span style="color: green; text-decoration: underline;">green</span>, deletions are shown in <span style="color:red; text-decoration: underline;">red</span>.</p>
    <table cellpadding="5" style="margin: auto">
        <tr>
            <td><label><input type="radio" name="diff_type" value="diffChars"> Diff by character &nbsp;&nbsp;</label></td>
            <td><label><input type="radio" name="diff_type" value="diffWords" checked> Diff by word &nbsp;&nbsp;</label></td>
            <td><label><input type="radio" name="diff_type" value="diffLines"> Diff by line &nbsp;&nbsp;</label></td>
        </tr>
    </table>
    <dl>
    @foreach ($entity->template->fields as $field)
        <dt>{{ $field->getInputLabel() }}</dt>
        <dd style="word-wrap:break-word;" data-from="{{ $previousEntity->getAttribute($field->name) }}" data-to="{{ $entity->getAttribute($field->name) }}"><pre></pre></dd>
    @endforeach
    </dl>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsdiff/3.2.0/diff.min.js"></script>
    <script>
    function diffIt(type) {
        var entityData = document.querySelectorAll('[data-from]');

        for (var elem of entityData) {

            var one = elem.getAttribute('data-from'),
                other = elem.getAttribute('data-to'),
                color = '',
                span = null;
            var diff = JsDiff[type](one, other),
                display = elem.querySelector('pre'),
                fragment = document.createDocumentFragment();
            display.innerHTML = '';
            diff.forEach(function(part){
              // green for additions, red for deletions
              // grey for common parts
              color = part.added ? 'green' :
                part.removed ? 'red' : 'grey';
              decoration = part.added || part.removed ? 'underline' : 'none';
              span = document.createElement('span');
              span.style.color = color;
              span.style['text-decoration'] = decoration;
              span.appendChild(document
                .createTextNode(part.value));
              fragment.appendChild(span);
            });
            display.appendChild(fragment);
        }
    }

    var radio = document.getElementsByName('diff_type');
    for (var i = 0; i < radio.length; i++) {
        radio[i].onchange = function(e) {
            diffIt(e.target.value);
        }
        if (radio[i].checked) {
            diffIt(radio[i].value);
        }
    }

    </script>
    <style type="text/css">
        [data-from] span {
            displ/*a*/y: block;
            word-break: break-all;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
    </style>
@stop