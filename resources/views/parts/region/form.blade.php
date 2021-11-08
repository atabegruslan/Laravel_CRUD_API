{!! Form::model($region, ['url' => !is_null($region) ? "region/$region->id" : 'region', 'enctype' => 'multipart/form-data', 'class' => 'form']) !!}

    {{ Form::hidden('user_id', Auth::user()->id) }}

    <div class="form-group">
        <label for="name">Region: </label>
        {{ Form::text('name', !is_null($region) ? $region->name : null, ['placeholder' => 'Region', 'class' => 'form-control', 'id' => 'name']) }}
    </div>

    @if (!is_null($region))
        {{ Form::hidden('_method', 'PUT') }}
    @endif

    {{ Form::token() }}

    {!! Form::submit(!is_null($region) ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
    
{!! Form::close() !!}
