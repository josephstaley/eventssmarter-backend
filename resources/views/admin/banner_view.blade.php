@extends('layouts.app')

@section('meta')
    <style>
        #editor {
            height: 50vh;
        }
    </style>
@endsection

@section('content')
    <div class="container">

        <h4><a href="/admin">Admin</a> / <a href="/admin/banner">Banners</a> / "{{ $banner->name }}"</h4>

        <a href="/admin/banner/{{ $banner->id }}/delete" onclick="return confirm('Are you really sure you want to delete all this data?')" class="btn text pull-right">Delete</a>
        <br>
        <h5>Name : {{$banner->name}}</h5>

        <h5>URL : {{$banner->link_url}}</h5>
        <h5>Click Counts : {{$banner->click_counts}}</h5>
        <img src={{$banner->image}} alt={{$banner->name}} style="width:100%;height:auto;">

    </div>
@endsection

@section('scripts')

@endsection