@extends('layouts.app')

@section('meta')
    <title>{{ trans('event.upcoming-events') }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="View all the upcoming events at happening at {{ env('APP_NAME') }}">
    <meta property="og:title" content="Upcoming Events at {{ env('APP_NAME') }}" />
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
    <meta property="og:description" content="View all the upcoming events at happening at {{ env('APP_NAME') }}" />
@endsection

@section('page-title')
    {{ trans('event.upcoming-events') }}
    <style>
        .pump {
            opacity: 0;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">

            <div class="col-md-12">

                <h1 class="title">{{ trans('models.event.upcoming_events_app', ['app_name' => env('APP_NAME')]) }}</h1>
                <hr>

                <div class="row">
                    <div class="col-md-3">
                        {{ request('query', 'Showing') }} {{ Str::plural(request('category', ''), 2) }} {{ trans('event.events') }} {{ ($city = request('city')) ? ' in ' . $city : '' }}
                        <br>
                        {{ $events->count() . ' of ' . $events->total() }}
                    </div>
                </div>

                <div class="row">

                    @foreach ($events as $event)
                        @include('partials.event_card')
                    @endforeach

                    @if ($events->isEmpty())
                        <div class="panel text-center">
                            <div class="panel-body">
                                {{ trans('models.event.no_event') }}
                            </div>
                        </div>
                    @endif

                </div>

                <div class="text-center">
                    {{ $events->render() }}
                </div>

            </div>
        </div>
    </div>
@endsection
