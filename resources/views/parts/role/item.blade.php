<table class="table">
    <thead>
        <tr>
            <th>Role: </th>
            <th>{{ $role->name }}</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>Permissions: </td>
            <td>
				@foreach($selectedPermissions as $selectedPermission)
					<p>{{ $selectedPermission->name }}</p>
				@endforeach
            </td>
        </tr>
    </tbody>
</table>
