@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.about') }} | {{ env('APP_NAME') }}</title>
    <style>
        .col-md-7, .col-md-6 {
            font-size: 18px;
        }
    </style>
@endsection

@section('content')
    <div class="container">

        <div class="about-page">
            @can('update',$content)
                <edit-content :content="{{ $content }}"></edit-content>
            @else
                {!! $content->body !!}
            @endcan
        </div>


        <br>
        <br>

    </div>
    <div class="hero section white-text">
        <Testimonials :quotes="{{ $quotes }}"></Testimonials>
    </div>

    <br>
    <br>

    <div class="col-md-4 col-md-offset-4">
        <a href="/decide" class="btn btn-lg btn-primary btn-block">{{ trans('general.are-you-ready-to-buy-sell-tickets') }}?</a>
    </div>
@endsection

@section('scripts')
    <script>
        window.sr = ScrollReveal();
        sr.reveal('.container');
    </script>
@endsection
