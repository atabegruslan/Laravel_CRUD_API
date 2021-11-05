@extends('layouts.app')

@section('title')
    Regions
@endsection

@section('content')

	@include('parts/menu/_crud_nav')
	
	@include('parts/msg/_success')

    <div class="vuepart">
        <Regions region-route="{{ url('/') }}/region"></Regions>
    </div>

@endsection
