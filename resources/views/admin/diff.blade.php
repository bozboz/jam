@extends('admin::layouts.default')

@section('main')
    <div class="form-row discrete">
        <a class="btn-default pull-right space-left btn btn-sm" href="{{ url()->previous() }}">
            <i class="fa fa-list-alt"></i>
            Back
        </a>
    </div>
    <h1>Diff for {{ $entity->name }}</h1>
    <h2><small>Showing changes made by {{ $revision->user->first_name }} {{ $revision->user->last_name }} at {{ $revision->created_at->format('H:i - d M Y') }}</small></h2>
    <p style="text-align:center">Additions are shown in <ins>green</ins>, deletions are shown in <del>red</del>.</p>
    <table cellpadding="5" style="margin: auto">
        <tr>
            <td><label><input type="radio" name="diff_type" value="diffChars"> Diff by character &nbsp;&nbsp;</label></td>
            <td><label><input type="radio" name="diff_type" value="diffWords" checked> Diff by word &nbsp;&nbsp;</label></td>
            <td><label><input type="radio" name="diff_type" value="diffLines"> Diff by line &nbsp;&nbsp;</label></td>
        </tr>
    </table>
    <dl class="js-diff">
    @foreach ($entity->template->fields as $field)
        <dt>{{ $field->getInputLabel() }}</dt>
        <dd style="word-wrap:break-word;" data-from="{{ $previousEntity->getAttribute($field->name) }}" data-to="{{ $entity->getAttribute($field->name) }}"><pre></pre></dd>
    @endforeach
    </dl>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsdiff/3.2.0/diff.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/autolinker/1.4.0/Autolinker.min.js"></script>
    <script>
    function diffIt(type) {
        var entityData = document.querySelectorAll('[data-from]');

        for (var elem of entityData) {

            var one = elem.getAttribute('data-from'),
                other = elem.getAttribute('data-to'),
                color = '',
                span = null;
            var diff = JsDiff[type](one, other),
                pre = elem.querySelector('pre'),
                fragment = document.createDocumentFragment();
            pre.innerHTML = '';
            diff.forEach(function(part){

              display = type === 'diffLines' ? 'block' : 'inline';
              span = document.createElement(part.added ? 'ins' : part.removed ? 'del' : 'span');
              span.style.display = display;
              span.appendChild(document
                .createTextNode(part.value));
              fragment.appendChild(span);
            });
            pre.appendChild(fragment);
        }

        var container = document.querySelector('.js-diff');

        container.innerHTML = Autolinker.link(container.innerHTML);
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
        [data-from] pre {
            word-break: break-all;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        ins {
            color: green;
            background: rgb(221, 255, 221);
            text-decoration: none;
        }
        del {
            color: red;
            background: rgb(255, 221, 221);
            text-decoration: none;
        }
    </style>
@stop