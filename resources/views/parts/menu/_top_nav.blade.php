<li class="nav-item">
	<a class="btn btn-link" href="{{ url('/') }}">Home</a>
</li>

<li class="nav-item">
	{{ link_to_route('entry.index', 'Places', [], ['class' => 'btn btn-link'] ) }}
</li>

<li class="nav-item">
	{{ link_to_route('contactform', 'Contact Us', [], ['class' => 'btn btn-link'] ) }}
</li>
