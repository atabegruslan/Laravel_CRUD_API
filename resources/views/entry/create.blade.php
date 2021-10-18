@extends('layouts.app')

@section('title')
    Entries
@endsection

@section('content')

    @include('parts/menu/_crud_nav')

    <div class="row" >

        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8">

        @include('parts/entry/form')

        </div>
        
    </div>

@endsection