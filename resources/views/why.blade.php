@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.about') }} {{ env('APP_NAME') }}</title>
    <style>
        .container .col-md-10 {
            font-size: 18px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        @can('update',$content)
            <edit-content :content="{{ $content }}"></edit-content>
        @else
            {!! $content->body !!}
        @endcan
    </div>
@endsection

@section('scripts')
    <script>
        window.sr = ScrollReveal();
        sr.reveal('.container');
    </script>
@endsection
