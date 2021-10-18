<table class="table">
    <thead>
        <tr>
            <th>Place: </th>
            <th>{{ $entry->place }}</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td><strong>Modified: </strong></td>
            <td>{{ $entry->updated_at }}</td>
        </tr>
        <tr>
            <td><strong>Review: </strong></td>
            <td>{{ $entry->comments }}</td>
        </tr>
    </tbody>
</table>
