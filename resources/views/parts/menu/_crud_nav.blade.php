<ul class="navbar-nav list-inline col-md-12">
	<div class='row'>
		<li class="nav-item">
			{{ link_to_route($feature . '.index', 'View All', [], ['class' => 'btn btn-primary'] ) }}
		</li>
		<li class="nav-item">
			{{ link_to_route($feature . '.create', 'Create New', [], ['class' => 'btn btn-primary'] ) }}
		</li>
		@if( isset($$feature) )
		<li class="nav-item">
			{{ link_to_route($feature . '.edit', 'Edit', [ $$feature->id ], ['class' => 'btn btn-primary'] ) }}
		</li>
		<li>
		    {!! Form::open(['url' => $feature . "/" . $$feature->id, 'enctype' => 'multipart/form-data', 'class' => 'form-inline']) !!}
		        {{ Form::hidden('_method', 'DELETE') }}
		        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
		    {!! Form::close() !!} 
		</li class="nav-item">
		@endif
	</div>
</ul>
