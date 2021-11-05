@extends('layouts.app')

@section('title')
    Region
@endsection

@section('content')

	@include('parts/menu/_crud_nav')

	<div class="row">        

	    <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
	                
	    @include('parts/region/item')

	    </div>

	</div>

@endsection