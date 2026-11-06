@extends('layouts.app')

@section('meta')
    <title>{{ trans('organization.organization-using') }} {{ env('APP_NAME') }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="Buy tickets online with {{ env('APP_NAME') }}">
    <meta name="keywords" content="tickets">
    <meta property="og:title" content="Organizations using {{ env('APP_NAME') }}" />
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
    <meta property="og:description" content="Buy your tickets online at {{ env('APP_NAME') }}.com" />
@endsection

@section('page-title')
    {{ ucwords($category) }} {{ trans('organization.organizations') }}
    @if ($state = request('state'))
        {{ trans('organization.in') }} {{ $state }}
    @endif
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <h1 class="title">{{ $category ?? '' }} {{ trans('organization.organization-using') }} {{ env('APP_NAME') }}</h1>
                <hr>

                <p>{{ trans('organization.showing') }} {{ $organizations->count() . trans('organization.of') . $organizations->total() }}</p>

                <div class="row">

                    @foreach ($organizations as $organization)
                        @include('partials.organization_card')
                    @endforeach

                </div>

                <div class="text-center">
                    {{ $organizations->render() }}
                </div>

            </div>
        </div>
    </div>
@endsection
