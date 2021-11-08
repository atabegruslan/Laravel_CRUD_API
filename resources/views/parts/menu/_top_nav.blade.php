<li class="nav-item">
	<div class="btn btn-link">
	{{ link_to_route('entry.index', 'Places', [], ['class' => 'btn btn-link'] ) }}
	</div>
</li>

<li>
	<div class="btn btn-link">
	{{ link_to_route('region.index', 'Regions', [], ['class' => 'btn btn-link'] ) }}
	</div>
</li>

@auth
@if (auth()->user()->hasRole('Admin'))
<li>
	<div class="btn btn-link">
	{{ link_to_route('user.index', 'Users', [], ['class' => 'btn btn-link'] ) }}
	</div>
</li>

<li>
	<div class="btn btn-link">
	{{ link_to_route('role.index', 'Roles', [], ['class' => 'btn btn-link'] ) }}
	</div>
</li>
@endif
@endauth

<li class="nav-item">
	<div class="btn btn-link">
	{{ link_to_route('contactform', 'Contact Us', [], ['class' => 'btn btn-link'] ) }}
	</div>
</li>

@auth
<li id="notices">
	<div class="dropdown">

		<button 
			class="dropbtn btn btn-link" 
			onclick="markNotificationsAsRead('{{ count(auth()->user()->unreadNotifications) }}')"
		>
			<span class="glyphicon glyphicon-globe"></span> 
			Notifications 
			<span class="badge">{{ count(auth()->user()->unreadNotifications) }}</span>
		</button>

		<div class="dropdown-content">
			@foreach(auth()->user()->unreadNotifications as $notification)
				<a href="{{ $notification->data['url'] }}">{{ $notification->data['name'] }}</a>
			@endforeach
		</div>
		
	</div>     
</li>
@endauth
