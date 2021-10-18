<ul class="navbar-nav list-inline col-md-12">
	<div class='row'>
		<li class="nav-item">
			{{ link_to_route('entry.index', 'View All', [], ['class' => 'btn btn-primary'] ) }}
		</li>
		<li class="nav-item">
			{{ link_to_route('entry.create', 'Create New', [], ['class' => 'btn btn-primary'] ) }}
		</li>
		@if(isset($entry))
		<li class="nav-item">
			{{ link_to_route('entry.edit', 'Edit', [ $entry->id ], ['class' => 'btn btn-primary'] ) }}
		</li>
		<li>
		    {!! Form::open(['url' => "entry/$entry->id", 'enctype' => 'multipart/form-data', 'class' => 'form-inline']) !!}
		        {{ Form::hidden('_method', 'DELETE') }}
		        {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
		    {!! Form::close() !!} 
		</li class="nav-item">
		@endif
	</div>
</ul>
