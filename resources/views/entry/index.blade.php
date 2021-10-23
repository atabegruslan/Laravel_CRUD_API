@extends('layouts.app')

@section('title')
    Entries
@endsection

@section('content')

	@include('parts/menu/_crud_nav')

	@include('parts/msg/_success')

	<div class="row">
	    <table class="table">
	        <thead>
	            <tr>
	                <th></th>
	                <th>Place</th>
	                <th>Review</th>
	                <th>User</th>
	                <th>Time</th>
	            </tr>
	        </thead>

	        <tbody>
	    	    @foreach($entries as $k => $v)
	    	    	<tr>
	                    <td>
	                        <img src="{{ $v->img_url }}" class="img-responsive small" alt="{{ $v->place }}">
	                    </td>
	                    <td>
	                    	<b>{{ link_to_route('entry.show', $v->place, [ $v->id ] ) }}</b>
	                    </td>
	                    <td>{!! $v->comments !!}</td>
	                    <td>{!! $v->user->name !!}</td>
	                    <td>{!! $v->updated_at !!}</td>
	    	    	</tr>
	    	    @endforeach
	        </tbody>
	    </table> 
	</div>

	<div class="row" >
	    <div id="paginate">
	        {{ $entries->links() }}
	    </div>
	</div>

@endsection
