<table class="table">
    <thead>
        <tr>
            <th>Place: </th>
            <th>{{ $entry->place }}</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td><strong>By: </strong></td>
            <td>{{ $entry->user->name }}</td>
        </tr>
        <tr>
            <td><strong>Modified: </strong></td>
            <td>{{ $entry->updated_at }}</td>
        </tr>
        <tr>
            <td><strong>Review: </strong></td>
            <td>{{ $entry->comments }}</td>
        </tr>

        <tr>
            <td><strong>Regions: </strong></td>
            <td>
                @foreach($entry->regions as $region)
                    <p class="region">{{ $region->name }}</p>
                @endforeach
            </td>
        </tr>
    </tbody>
</table>
