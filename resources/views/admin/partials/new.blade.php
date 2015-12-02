@if ($canCreate && $templates)
<div class="btn-group pull-right space-left">
	<button href="#" class="dropdown-toggle btn btn-primary" data-toggle="dropdown" aria-expanded="false">
		<i class="fa fa-plus-square"></i>
		New
		<span class="caret"></span>
		<span class="sr-only">Toggle Dropdown</span>
	</button>
	<ul class="dropdown-menu" role="menu">
		@foreach($templates as $template)
		<li>
			<a class="" type="submit" href="{{ action($createAction, [$template->alias]) }}">
				{{ $template->name }}
			</a>
		</li>
		@endforeach
	</ul>
</div>
@endif
