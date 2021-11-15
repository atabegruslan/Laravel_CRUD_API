@extends('layouts.app')

@section('title')
    Users
@endsection

@section('content')

@include('parts/menu/_crud_nav')

@include('parts/msg/_success')

<div class="row" >
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $k => $v)
            <tr>
                <td>
                    @if (auth()->user()->can('user.show'))
                    <b>{{ link_to_route('user.show', $v->name, [ $v->id ] ) }}</b>
                    @else
                    <b>{{ $v->name }}</b>
                    @endif
                </td>
                <td>{{ $v->email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="row" >
    <div id="paginate">
        {{ $users->links() }}
    </div>
</div>

@endsection
