@extends('layouts.app')

@section('title')
    Roles
@endsection

@section('content')

    @include('parts/menu/_crud_nav')

    @include('parts/msg/_success')

    <div class="row">
        <table class="table">
            <thead>
                <tr>
                    <th>Role</th>
                </tr>
            </thead>

            <tbody>
        	    @foreach($roles as $k => $v)
        	    	<tr>
                        <td>
                            @if (auth()->user()->can('role.show'))
                            <b>{{ link_to_route('role.show', $v->name, [ $v->id ] ) }}</b>
                            @else
                            <b>{{ $v->name }}</b>
                            @endif
                        </td>
        	    	</tr>
        	    @endforeach
            </tbody>
        </table> 
    </div>

@endsection
