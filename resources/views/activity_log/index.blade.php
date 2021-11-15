@extends('layouts.app')

@section('title')
    Activity Logs
@endsection

@section('content')

	@include('parts/msg/_success')

	<div class="row">
	    <table class="table">
	        <thead>
	            <tr>
	                <th>Actor</th>
	                <th>Object</th>
	                <th>Description</th>
	                <th>Data</th>
	                <th>Modified</th>
	            </tr>
	        </thead>

	        <tbody>
	    	    @foreach($logs as $k => $v)
	    	    	<tr>
	                    <td>{{ $v->actor ? link_to_route($v->actor_type.'.show', $v->actor->name, [ $v->actor->id ] ) : '' }}</td>
	                    <td>{{ $v->object ? link_to_route($v->object_type.'.show', $v->object->place, [ $v->object->id ] ) : '' }}</td>
	                    <td>{!! $v->description !!}</td>
	                    <td><pre>{!! $v->properties !!}</pre></td>
	                    <td>{!! $v->updated_at !!}</td>
	    	    	</tr>
	    	    @endforeach
	        </tbody>
	    </table> 
	</div>

	<div class="row" >
	    <div id="paginate">
	        {{ $logs->links() }}
	    </div>
	</div>

@endsection
