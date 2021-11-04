<table class="table">
    <thead>
        <tr>
            <th>Name: </th>
            <th>{{ $user->name }}</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Email: </strong></td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td><strong>Created: </strong></td>
            <td>{{ $user->created_at }}</td>
        </tr>

        <tr>
            <td><strong>Roles: </strong></td>
            <td>
                @foreach($selectedRoles as $selectedRole)
                    <p>{{ $selectedRole->name }}</p>
                @endforeach
            </td>
        </tr>
    </tbody>
</table>