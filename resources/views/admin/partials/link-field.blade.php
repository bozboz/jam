<fieldset{{ HTML::attributes($attributes) }}>
	@if (isset($legend))
		<legend>{{ $legend }}</legend>
	@endif
    <div class="clearfix" style="max-width: 700px;">
        @foreach ($fields as $field)
            <div style="width: 50%; float: left;">
                {!! $field->render($errors) !!}
            </div>
        @endforeach
    </div>
</fieldset>
