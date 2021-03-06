@extends('layouts.app')

@section('title')
    Entries
@endsection

@section('content')

	@include('parts/menu/_crud_nav')

	@include('parts/msg/_success')

	<form action="{{ route('entry.index') }}" method="GET" class="form form-inline">
	    <input type="text" name="search" required class="form-control" />
	    <button type="submit" class="btn btn-primary">Search</button>
	    <a href="{{ route('entry.index') }}">
	        <span class="input-group-btn">
	            <button class="btn btn-primary" type="button">Reset</button>
	        </span>
	    </a>
	</form>

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
	                        <img src="{{ url('/images/') . '/' . $v->img_url }}" class="img-responsive small" alt="{{ $v->place }}">
	                    </td>
	                    <td>
	                    	@if (auth()->user()->can('entry.show'))
	                    	<b>{{ link_to_route('entry.show', $v->place, [ $v->id ] ) }}</b>
	                    	@else
	                    	<b>{{ $v->place }}</b>
	                    	@endif
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
