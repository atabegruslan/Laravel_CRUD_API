@extends('layouts.app')

@section('title')
    User
@endsection

@section('content')

    @include('parts/menu/_crud_nav')

    @include('parts/msg/_success')

    <div class="row">        

        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8">

        @include('parts/user/form')

        </div>
            
        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    
        @include('parts/user/item')

        </div>

    </div>

@endsection