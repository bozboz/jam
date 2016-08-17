@extends('admin::layouts.default')

@section('main')

    {{ Form::model($template) }}

        <div class="form-group">
            {{ Form::label('name') }}
            {{ Form::text('name', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('alias') }}
            {{ Form::text('alias', null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group">
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
        </div>

        <button type="submit" class="btn btn-success pull-right">
            Duplicate
        </button>

    {{ Form::close() }}

@stop