@extends('admin::overview')

@section('report_header')
	@include('entities::admin.partials.new-entity')
	<h1>{{ $type->name or $heading }}</h1>

	@if (Session::has('model'))
		@foreach(Session::get('model') as $msg)
			<div id="js-alert" class="alert alert-success" data-alert="alert">
				{{ $msg }}
			</div>
		@endforeach
	@endif

	@include('admin::partials.sort-alert')

	{!! $report->getHeader() !!}
@stop

@section('report_footer')
	{!! $report->getFooter() !!}
	@include('entities::admin.partials.new-entity')
@stop
