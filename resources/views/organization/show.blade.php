@extends('layouts.app')

@section('meta')
    <title>{{ $organization->name }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="Buy tickets online for {{ $organization->name }}">
    <meta name="keywords" content="{{ $organization->name }},{{ $organization->name }} tickets">
    <meta property="og:title" content="{{ $organization->name }} Tickets" />
    <meta property="og:image" content="{{ $organization->cover_image }}" />
    <meta property="og:description" content="Tickets for {{ $organization->name }}: buy your tickets online at {{ env('APP_NAME') }}" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    <style>
        .banner-item a img {
            width: auto;
            height: 160px;
            margin: auto;
        }

        .slick-slide {
            height: 160px;
        }

        @media only screen and (max-width: 767px) {
            .banner-container {
                width: 100vw !important;
            }
        }
    </style>
@endsection

@section('page-title')
    {{ $organization->name }} {{ trans('organization.events') }}
@endsection

@section('content')
    <div class="container">
        @if (sizeof($banner))
            <div class="row">
                <div class="col-md-9 col-md-offset-3 col-sm-12 col-xs-12 banner-container">
                    <div class="event-banner">
                        @foreach ($banner as $key => $item)
                            <div class="banner-item">
                                <a href={{ $item->link_url }} target="_blank" onclick="banner_click({{ $item->id }})">
                                    <img data-u="image" src={{ $item->image }}>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="row" style="margin-top: 15px;">

            <div class="col-md-9 col-md-push-3">
                <div class="panel panel-default">
                    <div class="cover">
                        <img class="image" src="{{ $organization->cover_image }}" alt="{{ $organization->name }}">
                    </div>
                    <div class="panel-heading w-search">
                        <div class="row">
                            <div class="col-xs-3">
                                <div class="dropdown">
                                    <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                        {{ request('all') ? trans('organization.all-events') : trans('event.upcoming-events') }}
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                        <li role="presentation"><a href="?all={{ !request('all') }}">{{ trans('organization.view') }} {{ !request('all') ? trans('organization.all') : trans('organization.upcoming') }} {{ trans('organization.events') }}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        @if ($events->isEmpty())
                            <div>
                                {{ trans('organization.no-upcoming-events') }} @can('update', $organization)
                                    <a href="{{ route('events.create', $organization) }}">{{ trans('organization.add-new-event') }}</a>
                                @endcan
                                @if (!request('all'))
                                    {{ trans('organization.or') }} <a href="?all=true">{{ trans('organization.view-previous-events') }}.</a>
                                @endif
                            </div>
                        @elsecan('update',$organization)
                            <div>
                                <a href="{{ route('events.create', $organization) }}">{{ trans('organization.add-new-event') }}</a>
                                @if (!request('all'))
                                    {{ trans('organization.or') }} <a href="?all=true">{{ trans('organization.view-all-events') }}.</a>
                                @endif
                            </div>
                        @endif
                        @foreach ($events as $event)
                            <div class="panel-link row">
                                <div class="col-xs-2">
                                    <img src="{{ $event->image }}" alt="{{ $event->name }}" class="event-img">
                                </div>
                                <div class="col-xs-6">
                                    <h4>
                                        <a href="{{ $event->link('events.show') }}" class="text">
                                            {{ $event->name }}
                                        </a>
                                    </h4>
                                    <span class="text-detail">
                                        <a href="{{ $event->link('events.show') }}" class="text">
                                            <p>
                                                {{ $event->description }}
                                            </p>
                                            {{ trans('checkout.price') }}: {{ $event->price }} @if ($event->starts_at)
                                                | {{ trans('models.ticket.starts') }}: {{ $event->starts_at->diffForHumans() }}
                                            @endif | {{ trans('organization.where') }}: {{ $event->street }} {{ $event->city }} {{ $event->state }}
                                        </a>
                                    </span>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <br>
                                    <a href="{{ $event->link('events.show', ['checkout' => true]) }}" class="btn btn-primary">
                                        {{ $event->price }} &nbsp;<i class="fa fa-ticket"></i>
                                    </a>
                                    <br>
                                    <small>{{ trans('organization.reserve-your-tickets-online') }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="text-center">
                    {{ $events->render() }}
                </div>
            </div>

            @include('partials.organization_profile')

        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    <script type="module">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        function banner_click(id) {
            $.ajax({
                type:'POST',
                url:'/banner_click',
                data:{id:id},
                success:function(data){
                },
            });
        }
        $(document).ready(function() {
            $('.event-banner').slick({
                dots: false,
                infinite: true,
                speed: 500,
                slidesToShow: 4,
                autoplay: true,
                autoplaySpeed: 3000,
                arrows: false,
                pauseOnHover: true,
                responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3,
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 2
                        }
                    },
                    {
                        breakpoint: 230,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });
        });

    </script>
@endsection
