<ul class="navbar-nav list-inline col-md-12">
	<div class='row'>
		@if (auth()->user()->can($feature . '.index'))
		<li class="nav-item">
			{{ link_to_route($feature . '.index', 'View All', [], ['class' => 'btn btn-primary'] ) }}
		</li>
		@endif

		@if (!in_array(Route::currentRouteName(), ['user.index', 'user.show', 'user.edit']) && auth()->user()->can($feature . '.create'))
		<li class="nav-item">
			{{ link_to_route($feature . '.create', 'Create New', [], ['class' => 'btn btn-primary'] ) }}
		</li>
		@endif
		
		@if( isset($$feature) )
		@if (auth()->user()->can($feature . '.edit'))
		<li class="nav-item">
			{{ link_to_route($feature . '.edit', 'Edit', [ $$feature->id ], ['class' => 'btn btn-primary'] ) }}
		</li>
		@endif

		@if (auth()->user()->can($feature . '.destroy'))
		<li>
		    {!! Form::open(['url' => $feature . "/" . $$feature->id, 'enctype' => 'multipart/form-data', 'class' => 'form-inline']) !!}
		        {{ Form::hidden('_method', 'DELETE') }}
		        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
		    {!! Form::close() !!} 
		</li class="nav-item">
		@endif
		@endif
	</div>
</ul>
