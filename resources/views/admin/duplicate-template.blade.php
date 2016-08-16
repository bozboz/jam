@extends('admin::layouts.default')

@section('main')

    {{ Form::open() }}

        <fieldset>
            <legend>Types to copy to:</legend>
            <ul>
            @foreach ($types as $type)
                <li><label>
                    {{ Form::checkbox("types[]", $type->alias) }}
                    {{ $type->name }}
                </label></li>
            @endforeach
            </ul>
        </fieldset>

        <button type="submit" class="btn btn-success">
            Submit
        </button>

    {{ Form::close() }}

@stop