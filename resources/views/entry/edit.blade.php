@extends('layouts.app')

@section('title')
    Entry
@endsection

@section('content')

    @include('parts/menu/_crud_nav')

    <div class="row">        

        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8">

        @include('parts/entry/form')

        </div>
            
        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    
        @include('parts/entry/item')

        </div>

        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
            <img src="{{ $entry->img_url }}" class="img-responsive medium" alt="{{ $entry->place }}">
        </div>

        @include('parts/_entry_comments')

    </div>

@endsection