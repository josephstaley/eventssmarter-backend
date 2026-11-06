@extends('layouts.app')

@section('meta')
    <title>{{ $organization->name }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="Buy {{ $event->name }} tickets online for {{ $organization->name }}">
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

        .paginate_button {
            margin-right: 10px;
            cursor: pointer;
        }

        #inviteCrewTeamModal {
            top: 33% !important;
            transform: translate(0, -33%);
        }

        .morecontent span {
            display: none;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
        }

        .ticket-desc.more {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 6;
            line-clamp: 6;
            -webkit-box-orient: vertical;
            word-wrap: break-word;
        }

        .main-banner {
            width: 100%;
            padding-bottom: 40px;
            border-radius: 0.8rem;
        }

        .price {
            font-size: 16pt;
            color: black;
            margin: auto;
        }

        .ticket-price-col {
            margin-top: 20px;
        }

        .ticket-price-col button {
            min-width: 120px;
            width: auto;
            margin: auto;
        }

        .event-desc.more {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 7;
            -webkit-box-orient: vertical;
            overflow-x: hidden;
        }

        .morelink {
            float: right;
            cursor: pointer;
        }

        .ticket-desc-morelink {
            display: block;
            cursor: pointer;
        }

        .video-container {
            margin: 30px auto;
            text-align: center;
        }

        .flex-flow-wrap {
            flex-flow: wrap;
        }

        .no-padding {
            padding: 0px !important;
        }

        .ticket-desc {
            white-space: pre-line;
            word-wrap: break-word;
        }
    </style>

    @if ($event->fb_pixel_id)
        <script>
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ?
                        n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo $event->fb_pixel_id; ?>');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo $event->fb_pixel_id; ?>&ev=PageView&noscript=1" /></noscript>
    @endif
    @if ($event->ga_measure_id)
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $event->ga_measure_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ $event->ga_measure_id }}');
        </script>
    @endif
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">

    @if ($event->video)
        <link href="https://vjs.zencdn.net/8.0.4/video-js.css" rel="stylesheet" />
        <!-- Fantasy -->
        <link href="https://unpkg.com/@videojs/themes@1/dist/fantasy/index.css" rel="stylesheet">
    @endif
@endsection

@section('page-title')
    {{ $event->name }} @ {{ $organization->name }}
@endsection

@section('content')
    <div class="container" style="padding-top:0px;">
        @if ($mainBanner)
            <img class="main-banner" src={{ $mainBanner->image }} alt="Main banner" />
        @endif
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

            @if (Gate::check('update', $event) || \App\Models\User::isAdmin())
                @if ($event->sales()->tickets > 0)
                    @include('partials.sale_overview')
                @else
                    <div class="col-md-12">
                        <p>{{ trans('event.no-ticket-sales-yet') }}, <a href="{{ route('sales.show', $event) }}">{{ trans('event.view-ticket-sales') }}</a> {{ trans('event.anyway') }}.
                        </p>
                    </div>
                @endif
            @endif

            <div class="col-md-9 col-md-push-3">

                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">

                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <img class="event-cover w-full" src="{{ $event->image }}" alt="{{ $event->name }}">
                                    </div>
                                    <div class="col-sm-8 event-title-info">
                                        <h1 class="event-title">{{ $event->name }}
                                            @can('update', $event)
                                                <a href="{{ route('events.edit', [$organization, $event->id]) }}" class="pull-right">
                                                    <i class="fa fa-pencil-square"></i>
                                                    {{ trans('event.edit-event-information') }}
                                                </a>
                                                <a href="{{ route('events.advertise', [$organization, $event->id]) }}" class="pull-right" style="margin-right:20px">
                                                    {{ trans('event.advertise') }}
                                                </a>
                                            @endcan
                                        </h1>
                                        <p class="event-desc more">{!! nl2br(e($event->description)) !!}</p>
                                        <a class="morelink">{{ trans('event.show-more') }}</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="times text-center">
                                            @if ($event->starts_at)
                                                <div class="col-md-5 col-xs-5">
                                                    <h4>
                                                        {{ $event->starts_at->format('l M j, Y') }}<br>
                                                        {{ $event->starts_at->format('g:i A') }}<br>
                                                    </h4>
                                                </div>
                                                <div class="col-md-1 col-xs-2">
                                                    {{ trans('event.to') }}
                                                </div>
                                                <div class="col-md-5 col-xs-5">
                                                    <h4>
                                                        {{ $event->ends_at->format('l M j, Y') }}<br>
                                                        {{ $event->ends_at->format('g:i A') }}<br>
                                                    </h4>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <a class="btn btn-default tooltip-text" style="position:absolute;right:15px;top:0px;" onclick="getShareEventLink(this)">
                                    <i class="fa fa-share-alt"></i>
                                    <span class="tooltiptext">{{ trans('get-share-link') }}</span>
                                </a>
                                @if ($event->video)
                                    <div class="video-container">
                                        <video id="my-player" class="video-js vjs-theme-fantasy" controls preload="auto" data-setup='{"fluid":true, "responsive": true}'>
                                            <source src="{{ $event->video }}">
                                            <p class="vjs-no-js">
                                                To view this video please enable JavaScript, and consider upgrading to a web browser that
                                                <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                                            </p>
                                        </video>
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>

                @if ($event->attachments && count($event->attachments))
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.attachments') }} ({{ count($event->attachments) }})
                        </div>
                        <div class="panel-body">

                            <table style="width: 100%">
                                <tbody>
                                    @foreach ($event->attachments as $pdf)
                                        <tr>
                                            <td><a href="{{ $pdf->file_name }}" target="_blank" class="btn btn-primary-outline btn-xs">
                                                    {{ $pdf->name }}
                                                </a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                    </div>
                @endif

                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.tickets') }}
                        @can('update', $event)
                            <span class="pull-right">
                                <a href="{{ $event->link('tickets.create') }}">
                                    <i class="fa fa-ticket"></i> {{ trans('event.add-tickets') }}
                                </a>
                            </span>
                            @if ($video_call_ticket_available == 1)
                                <span class="pull-right" style="padding-right: 20px">
                                    <a style="cursor: pointer" onclick="showCallSchedules()">
                                        <i class="fa fa-clock-o"></i> {{ trans('event.call-schedules') }}
                                    </a>
                                </span>
                            @endif
                            @if (count($live_streams) > 0)
                                <span class="pull-right" style="padding-right: 20px">
                                    <a style="cursor: pointer" onclick="showLiveStreams()">
                                        <i class="fa fa-clock-o"></i> {{ trans('event.live-streams') }}
                                    </a>
                                </span>

                                <span class="pull-right" style="padding-right: 20px">
                                    <a style="cursor: pointer" onclick="showInviteCameraCrewTeam()">
                                        <i class="fa fa-user-plus"></i> {{ trans('event.invite-camera-crew-team') }}
                                    </a>
                                </span>
                            @endif
                        @endcan
                    </div>
                    <div class="panel-body">
                        @foreach ($event->tickets as $ticket)
                            <form class="panel-link row" action="/checkout">
                                <input type="hidden" name="id" value="{{ $ticket->id }}">
                                <div class="col-md-7 col-xs-7 ticket-info">
                                    <h4>
                                        <span class="text">
                                            {{ $ticket->name }}
                                        </span>
                                    </h4>
                                    <span class="text-detail">
                                        <p>
                                            <span class="ticket-desc more ticket-desc-{{ $ticket->id }}">
                                                {!! nl2br(\App\Models\Helpers::linkify($ticket->description)) !!}
                                            </span>
                                            <a class="ticket-desc-morelink ticket-desc-morelink-{{ $ticket->id }}">{{ trans('event.show-more') }}</a>
                                            @can('update', $ticket)
                                                <a href="{{ route('tickets.edit', [$organization, $ticket->id]) }}" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                @if ($ticket->is_live_stream)
                                                    <a class="btn btn-primary btn-xs tooltip-text" style="margin-left:4px;" onclick="getInviteLink({{ $ticket->id }}, '{{ $encrypted_login_token }}', this, '{{ $ticket->stream_url }}')">
                                                        <i class="fa fa-share-alt"></i>
                                                        <span class="tooltiptext">{{ trans('event.get-invitation-link') }}</span>
                                                    </a>
                                                    @if (strpos($ticket->stream_url, 'live.eventssmarter.com/live'))
                                                        <a class="btn btn-primary btn-xs tooltip-text" style="margin-left:4px;" onclick="downloadAgoraConfig({{ $ticket->id }})">
                                                            <i class="fa fa-download"></i>
                                                            <span class="tooltiptext" style="width: 160px">{{ trans('event.download-config-file-for-obs') }}</span>
                                                        </a>
                                                    @endif
                                                @endif
                                            @else
                                                @if ($cameraCrew)
                                                    <a class="btn btn-primary btn-xs tooltip-text" style="margin-left:4px;" onclick="getInviteLink({{ $ticket->id }}, '{{ $encrypted_login_token }}', this, '{{ $ticket->stream_url }}')">
                                                        <i class="fa fa-share-alt"></i>
                                                        <span class="tooltiptext">{{ trans('event.get-invitation-link') }}</span>
                                                    </a>
                                                    @if (strpos($ticket->stream_url, 'live.eventssmarter.com/live'))
                                                        <a class="btn btn-primary btn-xs tooltip-text" style="margin-left:4px;" onclick="downloadAgoraConfig({{ $ticket->id }})">
                                                            <i class="fa fa-download"></i>
                                                            <span class="tooltiptext" style="width: 160px">{{ trans('event.download-config-file-for-obs') }}</span>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endcan
                                        </p>
                                    </span>
                                </div>
                                <div class="col-md-5 col-xs-5 text-center ticket-price-col">
                                    <div class="row">
                                        <div class="col-xs-12 flex flex-flow-wrap no-padding">
                                            @if ($ticket->is_donation)
                                                <span class="price col-md-6 col-xs-12"></span>
                                                <button type="submit" class="btn btn-primary o ticket-price col-md-6 col-xs-12">{{ trans('event.donate') }}</button>
                                            @elseif($ticket->is_call)
                                                <span class="price col-md-6 col-xs-12">
                                                    {{ $ticket->price }}
                                                    {{ $ticket->event->organization->currency === 'USD' ? '' : '(' . $ticket->event->organization->currency . ')' }}
                                                </span>
                                                <button type="submit" class="btn btn-primary o ticket-price col-md-6 col-xs-12">{{ trans('event.book') }}</button>
                                            @elseif($ticket->is_reservation)
                                                <span class="price col-md-6 col-xs-12">
                                                    {{ $ticket->price }}
                                                    {{ $ticket->event->organization->currency === 'USD' ? '' : '(' . $ticket->event->organization->currency . ')' }}
                                                </span>
                                                <button type="submit" class="btn btn-primary o ticket-price col-md-6 col-xs-12">{{ trans('event.reserve') }}</button>
                                            @elseif($ticket->is_seat)
                                                <span class="price col-md-6 col-xs-12">
                                                    {{ $ticket->price }}
                                                    {{ $ticket->event->organization->currency === 'USD' ? '' : '(' . $ticket->event->organization->currency . ')' }}
                                                </span>
                                                <button type="submit" class="btn btn-primary o ticket-price col-md-6 col-xs-12">{{ trans('event.choose-seat') }}</button>
                                            @elseif($ticket->cents == 0)
                                                <span class="price col-md-6 col-xs-12">
                                                    {{ trans('event.free') }}
                                                </span>
                                                <button :disabled="{{ $ticket->quantity == 0 ? 'true' : 'false' }}" type="button" class="btn btn-primary o ticket-price col-md-6 col-xs-12"
                                                    @click="emitter.emit('addToCart', {{ $ticket->id }})">{{ $ticket->quantity == 0 ? trans('event.sold-out') : trans('event.add-to-cart') }}</button>
                                            @else
                                                <span class="price col-md-6 col-xs-12">
                                                    {{ $ticket->price }}
                                                    {{ $ticket->event->organization->currency === 'USD' ? '' : '(' . $ticket->event->organization->currency . ')' }}
                                                </span>
                                                <button :disabled="{{ $ticket->quantity == 0 ? 'true' : 'false' }}" type="button" class="btn btn-primary o ticket-price col-md-6 col-xs-12"
                                                    @click="emitter.emit('addToCart', {{ $ticket->id }})">{{ $ticket->quantity == 0 ? trans('event.sold-out') : trans('event.add-to-cart') }}</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endforeach
                        @if ($event->tickets->isEmpty())
                            <p class="text-center">
                                {{ trans('event.ticket-no-sale') }}
                            </p>
                        @endif
                    </div>
                </div>
                @can('update', $event)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.feedback') }} ({{ count($feedbacks) }})
                        </div>
                        <div class="panel-body">

                            <table style="width: 100%">
                                <thead>
                                    <tr>
                                        <th width="30%">{{ trans('auth.name') }}</th>
                                        <th width="30%">{{ trans('event.rating') }}</th>
                                        <th width="30%">{{ trans('event.review') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($feedbacks as $row)
                                        <tr>
                                            <td>{{ $row->user->name }}</td>
                                            <td>{{ $row->rating }}</td>
                                            <td>{{ $row->review }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $feedbacks->links() }}
                            <a href="{{ route('event.event_feedbacks', ['event' => $event->id]) }}">{{ trans('event.show-all-feedback') }}</a>
                        </div>
                    </div>
                @endcan

                @if (Gate::check('update', $event) || \App\Models\User::isAdmin())
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.purchasers') }} ({{ $purchasers_count }})
                        </div>
                        <div class="panel-body">
                            <table class="table table-hover" id="purchasers_list" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('event.avatar') }}</th>
                                        <th>{{ trans('auth.name') }}</th>
                                        <th>{{ trans('auth.email') }}</th>
                                        <th>{{ trans('checkout.phone-number') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                @endif

                @if (Gate::check('update', $event) || \App\Models\User::isAdmin())
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.notification') }}
                        </div>
                        <div class="panel-body">
                            <form action="{{ route('events.notification', [$organization, $event->id]) }}" method="post" id="payment-form" enctype="multipart/form-data">

                                {{ csrf_field() }}

                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                <div class="row ">
                                    <div class="col-md-3">
                                        {{ trans('event.notifiaction-subject') }}
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                            <input name="subject" id="subject" class="form-control">

                                            @if ($errors->has('subject'))
                                                <div class="text-danger">{{ $errors->first('subject') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-md-3">
                                        {{ trans('event.message') }}
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                            <textarea name="message" id="message" class="form-control"></textarea>

                                            @if ($errors->has('message'))
                                                <div class="text-danger">{{ $errors->first('message') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-md-3">
                                        {{ trans('event.attach-image') }}
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                            <input type="file" name="attach_img" id="attach_img" class="form-control" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-md-3">
                                        {{ trans('event.attach-file') }}
                                    </div>
                                    <div class="col-md-9">
                                        <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                            <input type="file" name="attach_file" id="attach_file" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <br>

                                <div class="row">
                                    <div class="col-md-11">
                                        <button class="btn btn-primary" id="pay-btn">{{ trans('event.send') }}</button>
                                        @php
                                            $today = date('Y-m-d');
                                            $endDate = date('Y-m-d', strtotime($event->ends_at));
                                        @endphp

                                        {{-- @if ($endDate < $today && count($purchasers_count) > 0) <button data-event="{{$event->id}}" data-purchasers="{{$user_ids}}" type="button" class="btn btn-primary" id="feedback-btn">Get Users Feedback</button>
                                @endif --}}
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                @endif

                @can('update', $event)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-advertising') }} ({{ count($banner) }})
                            <span class="pull-right">
                                <a href="{{ $event->link('banners.create') }}">
                                    <i class="fa fa-ticket"></i> {{ trans('event.add-banners') }}
                                </a>
                            </span>
                        </div>
                        <div class="panel-body">

                            <table style="width: 100%">
                                <thead>
                                    <tr>
                                        <th width="25%">{{ trans('auth.name') }}</th>
                                        <th width="45%">{{ trans('event.link-url') }}</th>
                                        <th width="10%">{{ trans('event.click-counts') }}</th>
                                        <th width="10%">{{ trans('event.main-banner') }}</th>
                                        <th width="5%">{{ trans('general.menu.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($mainBanner)
                                        <tr>
                                            <td>
                                                {{ $mainBanner->name }}
                                            </td>
                                            <td>{{ $mainBanner->link_url }}</td>
                                            <td>{{ $mainBanner->click_counts }}</td>
                                            <td>Yes</td>
                                            <td>
                                                <a href="{{ route('banners.edit', [$organization, $mainBanner->id]) }}" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach ($banner as $row)
                                        <tr>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->link_url }}</td>
                                            <td>{{ $row->click_counts }}</td>
                                            <td>{{ trans('event.no') }}</td>
                                            <td>
                                                <a href="{{ route('banners.edit', [$organization, $row->id]) }}" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endcan

                @if (($event->lat && $event->lng) || $event->canEdit())
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-location') }}
                            @can('update', $event)
                                <a href="{{ route('location', $event) }}" class="pull-right"><i class="fa fa-map-marker"></i>
                                    {{ trans('event.specify-event-location') }}</a>
                            @endcan
                        </div>
                        @if ($event->lat && $event->lng)
                            <div class="panel-body">
                                <a href="{{ $event->mapLink() }}" target="_blank">
                                    <img class="w-full" src="https://maps.googleapis.com/maps/api/staticmap?center={{ $event->lat }},{{ $event->lng }}&markers={{ $event->lat }},{{ $event->lng }}&size=640x340&sensor=true&zoom=5&scale=1&key={{ env('GOOGLE_MAPS_API_FRONT_KEY') }}"
                                        alt="{{ $event->name }}">
                                </a>
                                <div class="directions text-center">
                                    <p>
                                    <h3>{{ $event->location_name }}</h3>
                                    {{ $event->address }} <br>
                                    </p>
                                    <a class="text" href="{{ $event->mapLink() }}" target="_blank">
                                        {{ trans('event.view-map') }}
                                    </a> |
                                    <a class="text" href="{{ $event->mapLink() }}" target="_blank">
                                        {{ trans('event.get-directions') }}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            <div class="modal fade" id="callScehdulesModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">{{ trans('event.upcoming-calls') }}</h4>
                        </div>
                        <div class="modal-body">
                            <table class="table" id="upcomingCallsTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{ trans('event.no') }}</th>
                                        <th class="text-center">{{ trans('auth.name') }}</th>
                                        <th class="text-center">{{ trans('event.ticket') }}</th>
                                        <th class="text-center">{{ trans('event.scheduled-time') }}</th>
                                        <th class="text-center">{{ trans('event.call-access-link') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($call_vouchers as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key }}</td>
                                            <td class="text-center">{{ $item->user()->first()->name }}</td>
                                            <td class="text-center">{{ $item->ticket->name }}</td>
                                            <td class="text-center">
                                                {{ date_format(date_create($item->call_start_time), 'l, M d,  h:i') }} -
                                                {{ date_format(date_add(date_create($item->call_start_time), date_interval_create_from_date_string('1 hours')), 'h:i') }}
                                            </td>
                                            <td class="text-center">
                                                <a target="blank" style="color: #fb0;" href="https://call.eventssmarter.com/rooms/{{ $item->id }}?{{ $login_token }}">
                                                    {{ trans('event.access') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('event.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="liveStreamModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">{{ trans('event.upcoming-live-streams') }}</h4>
                        </div>
                        <div class="modal-body">
                            <table class="table" id="upcomingLiveStreamsTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">{{ trans('event.no') }}</th>
                                        <th class="text-center">{{ trans('event.ticket') }}</th>
                                        <th class="text-center">{{ trans('event.live-stream-link') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($live_streams as $key => $item)
                                        <tr>
                                            <td class="text-center">{{ $key }}</td>
                                            <td class="text-center">{{ $item->name }}</td>
                                            <td class="text-center">
                                                <a target="blank" style="color: #fb0;" href="{{ strpos($item->stream_url, 'live.eventssmarter.com/live') ? $item->stream_url . '?' . $login_token : route('tickets.stream', [$item->event->organization, $item->id]) }}">
                                                    {{ trans('event.access') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('event.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="inviteCrewTeamModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">{{ trans('event.invite-camera-crew-team') }}</h4>
                        </div>
                        <div class="modal-body">
                            <input type="text" placeholder="{{ trans('event.input-email-address') }}" class="form-control" id="inviteEmail">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="sendCameraCrewInvite({{ $event->id }})">{{ trans('event.invite') }}</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('event.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            @include('partials.organization_profile')
            @can('update', $event)
                @include('partials.modals.gettheapp')
            @endcan

        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js" defer></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    <script type="module">
        localStorage.setItem('promo_code', '{{ $promo_code }}')
        $(function() {

            $('#feedback-btn').click(function() {
                var pur = $(this).data('purchasers');
                var event = $(this).data('event')
                if (pur != '' && event != '') {
                    $.ajax({
                        type: 'get',
                        url: '/send_feedback_mail',
                        data: {
                            pur: pur,
                            event: event
                        },
                        success: function(data) {
                            alert('Feedback email has been sent to all purchasers.')
                        },
                    });
                }
            })

        })
    </script>

    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    @if ($event->video)
        <script src="https://vjs.zencdn.net/8.0.4/video.min.js" defer></script>
    @endif
    <script type="module">
        @if ($showPendingSubscription || session('showPendingSubscription'))
            swal({
                text: "{{ trans('event.you-subscribed-success') }}!",
                icon: "success"
            });
        @endif
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        window.banner_click = (id) => {
            $.ajax({
                type: 'POST',
                url: '/banner_click',
                data: {
                    id: id
                },
                success: function(data) {},
            });
        }

        function urlify(text) {
            var urlRegex = /(https?:\/\/[^\s]+)/g;
            return text.replace(urlRegex, function(url) {
                return '<a target="blank" href="' + url + '">' + url + '</a>';
            })
        }

        $(document).ready(function() {
            var event_desc_element = $('.event-desc')[0];
            if (event_desc_element.offsetHeight < event_desc_element.scrollHeight || event_desc_element
                .offsetWidth < event_desc_element.scrollWidth) {
                // your event_desc_element has an overflow
                $(".morelink").click(function() {
                    if ($('.event-desc').hasClass("more")) {
                        $('.event-desc').removeClass("more");
                        $(this).html('{{ trans("event.show-less") }}');
                    } else {
                        $('.event-desc').addClass("more");
                        $(this).html('{{ trans("event.show-more") }}');
                    }
                });
            } else {
                // your event_desc_element doesn't have overflow
                $(".morelink").css({
                    display: "none"
                });
            }
            @foreach ($event->tickets as $ticket)
                if ($('.ticket-desc-{{ $ticket->id }}')[0].offsetHeight < $(
                        '.ticket-desc-{{ $ticket->id }}')[0].scrollHeight || $(
                        '.ticket-desc-{{ $ticket->id }}')[0].offsetWidth < $(
                        '.ticket-desc-{{ $ticket->id }}')[0].scrollWidth) {
                    // your ticket_desc_element has an overflow
                    $(".ticket-desc-morelink-{{ $ticket->id }}").click(function() {
                        if ($('.ticket-desc-{{ $ticket->id }}').hasClass("more")) {
                            $('.ticket-desc-{{ $ticket->id }}').removeClass("more");
                            $(this).html('{{ trans("event.show-less") }}');
                        } else {
                            $('.ticket-desc-{{ $ticket->id }}').addClass("more");
                            $(this).html('{{ trans("event.show-more") }}');
                        }
                    });
                } else {
                    // your ticket_desc_element doesn't have overflow
                    $(".ticket-desc-morelink-{{ $ticket->id }}").css({
                        display: "none"
                    });
                }
            @endforeach

            let adsid = window.location.search.substring(1)
            if (adsid && adsid !== 'show_code=1') {
                localStorage.setItem('adsid', adsid)
            }

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
            $('#upcomingCallsTable').DataTable({
                "columnDefs": [{
                        "targets": 4,
                        "orderable": false,
                    },
                    {
                        "targets": 3,
                        "orderable": false,
                    }
                ]
            });
            $('#upcomingLiveStreamsTable').DataTable({
                "columnDefs": [{
                    "targets": 2,
                    "orderable": false,
                }]
            });
            $('#purchasers_list').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('event.getPurchasers') }}",
                    type: 'GET',
                    data: {
                        event_id: {{ $event->id }}
                    }
                },
                pageLength: 5,
                searchDelay: 1500,
                "lengthMenu": [
                    [5, 10, 25, 50, 100],
                    [5, 10, 25, 50, 100]
                ],
                columns: [{
                        data: 'avatar',
                        render: function(data, type, row, meta) {
                            return '<img src="' + row.avatar + '" class="user-avatar" />';
                        }
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'phone_number'
                    }
                ],
                "columnDefs": [{
                    "targets": 0,
                    "orderable": false,
                }],
                "language": {
                    "sInfo": "Showing _START_ to _END_ of _TOTAL_ Purchasers",
                    "sInfoEmpty": "Showing 0 to 0 of 0 Purchasers",
                    "infoFiltered": "",
                    oPaginate: {
                        sNext: '<i class="fa fa-forward"></i>',
                        sPrevious: '<i class="fa fa-backward"></i>',
                        sFirst: '<i class="fa fa-step-backward"></i>',
                        sLast: '<i class="fa fa-step-forward"></i>'
                    }
                }
            });

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });

        window.showCallSchedules = () => {
            $('#callScehdulesModal').modal('show')
        }

        window.showLiveStreams = () => {
            $('#liveStreamModal').modal('show')
        }

        window.getInviteLink = (ticket_id, login_token, target, stream_url) => {
            var textArea = document.createElement("textarea");
            if (stream_url.indexOf('live.eventssmarter.com/live') !== -1) {
                textArea.value = "https://live.eventssmarter.com/live/" + ticket_id + "?invite=" + login_token;
            } else {
                textArea.value = stream_url;
            }
            textArea.style.opacity = "0";
            target.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                swal({
                    text: "{{ trans('event.invitation-link-copied') }}!",
                    icon: "success"
                });
            } catch (err) {
                alert('Unable to copy value , error : ' + err.message);
            }
            target.removeChild(textArea);
        }

        window.getShareEventLink = (target) => {
            let current_url = window.location.href;
            if (current_url.includes('show_code=1')) {
                current_url = current_url.replace('show_code=1', '');
            }
            var textArea = document.createElement("textarea");
            textArea.value = current_url;
            textArea.style.opacity = "0";
            target.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                swal({
                    text: "{{ trans('event.event-share-link-copied') }}!",
                    icon: "success"
                });
            } catch (err) {
                alert('Unable to copy value , error : ' + err.message);
            }
            target.removeChild(textArea);
        }
    </script>
    @if (auth()->user() || $cameraCrew)
        <script type="module">
            window.downloadAgoraConfig = (ticket_id) => {
                $.ajax({
                    type: 'GET',
                    url: `/event/${ticket_id}/live/token`,
                    success: function(data) {
                        var data = JSON.parse(data)
                        if (data.success) {
                            var element = document.createElement('a');
                            const textContent =
                                `[BaseInfo]\nAppId=<?php echo env('AGORA_APP_ID'); ?>\nAppToken=${data.data.token}\nUID=${data.data.uid}\nChannelName=live-${ticket_id}\nRtmpUrl=`;
                            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(
                                textContent));
                            element.setAttribute('download', 'config.ini');
                            element.style.display = 'none';
                            document.body.appendChild(element);
                            element.click();
                            document.body.removeChild(element);
                        }
                    },
                });
            }

            window.showInviteCameraCrewTeam = () => {
                $('#inviteEmail').val('');
                $('#inviteCrewTeamModal').modal('show');
            }

            window.sendCameraCrewInvite = (event_id) => {
                let email = $('#inviteEmail').val().trim();
                if (email !== '') {
                    $('#inviteCrewTeamModal').modal('hide');
                    swal({
                        text: "{{ trans('event.invitation-email-sent') }}!",
                        icon: "success"
                    });
                    $.ajax({
                        type: 'POST',
                        url: `/event/${event_id}/inviteCamereCrew`,
                        data: {
                            email
                        }
                    });
                }
            }
        </script>
    @endif
@endsection
