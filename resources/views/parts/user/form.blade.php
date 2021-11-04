{!! Form::model($user, ['url' => !is_null($user) ? "user/$user->id" : 'user', 'enctype' => 'multipart/form-data', 'class' => 'form']) !!}

    {{ Form::hidden('user_id', Auth::user()->id) }}

    <div class="form-group">
        <label for="name">User: </label>
        {{ Form::text('name', !is_null($user) ? $user->name : null, ['placeholder' => 'User', 'class' => 'form-control', 'id' => 'name', 'readonly']) }}
    </div>

    <div class="form-group">
        <label for="role_ids">Roles: </label>
        <select name="role_ids[]" id="role_ids" class="form-control" multiple>
             @foreach($roles as $role)
                @if(in_array($role->id, $selectedRoleIds))
                 <option value="{{ $role->id }}" selected>
                @else
                 <option value="{{ $role->id }}">
                @endif
                     {{ $role->name }}
                 </option>
             @endforeach
        </select>
    </div>

    @if (!is_null($user))
        {{ Form::hidden('_method', 'PUT') }}
    @endif

    {{ Form::token() }}

    {!! Form::submit(!is_null($user) ? 'Update' : 'Create', ['class' => 'btn btn-default']) !!}
    
{!! Form::close() !!}
