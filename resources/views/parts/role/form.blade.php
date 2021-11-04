{!! Form::model($role, ['url' => !is_null($role) ? "role/$role->id" : 'role', 'enctype' => 'multipart/form-data', 'class' => 'form']) !!}

    {{ Form::hidden('user_id', Auth::user()->id) }}

    <div class="form-group">
        <label for="name">Role: </label>
        {{ Form::text('name', !is_null($role) ? $role->name : null, ['placeholder' => 'Role', 'class' => 'form-control', 'id' => 'name']) }}
    </div>

    <div class="form-group">
        <label for="permission_ids">Permissions: </label>
        <select name="permission_ids[]" id="permission_ids" class="form-control" multiple>
             @foreach($permissions as $permission)
                @if(in_array($permission->id, $selectedPermissionIds))
                 <option value="{{ $permission->id }}" selected>
                @else
                 <option value="{{ $permission->id }}">
                @endif
                     {{ $permission->name }}
                 </option>
             @endforeach
        </select>
    </div>

    @if (!is_null($role))
        {{ Form::hidden('_method', 'PUT') }}
    @endif

    {{ Form::token() }}

    {!! Form::submit(!is_null($role) ? 'Update' : 'Create', ['class' => 'btn btn-default']) !!}
    
{!! Form::close() !!}
