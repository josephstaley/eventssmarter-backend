@extends('layouts.app')

@section('page-title')
    {{ trans('event.your-tickets-for') }} {{ $event->name }}
@endsection
@section('style')
    <style>
        .swal-text {
            text-align: center;
        }

        .swal-footer {
            text-align: center;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.tickets') }}
                        @if ($vouchers->count() > 5)
                            <a href="{{ route('voucher.download', [$vouchers->pluck('id')->implode(',')]) }}" target="_blank" class="btn btn-sm btn-default pull-right">
                                <i class="fa fa-download"></i> {{ trans('event.download-all') }}
                            </a>
                        @endif
                    </div>

                    <?php $amount = new \NumberFormatter('us_US', \NumberFormatter::CURRENCY); ?>
                    <div class="panel-body">

                        @foreach ($vouchers as $voucher)
                            <?php $ticket = $voucher
                                ->ticket()
                                ->withTrashed()
                                ->first(); ?>
                            <div class="panel-link">
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        <img src="{{ $event->image }}" alt="{{ $event->name }}" class="event-img">
                                    </div>
                                    <div class="col-md-7">
                                        <h4>
                                            <a href="{{ $ticket->event->link('events.show') }}" class="text">
                                                {{ $ticket->name }} @ {{ $ticket->event->organization->name }}
                                            </a>
                                        </h4>
                                        <h5 class="text-detail">
                                            {{ $voucher->ticket_bundle ? $voucher->ticket_bundle->count : ' ' }} {{ trans('event.ticket-bundle') }} |
                                            {{ $voucher->ticket_bundle ? ($voucher->ticket_bundle->bundle_name == 0 || $voucher->ticket_bundle->bundle_name == 1 ? 'Normal' : ($voucher->ticket_bundle && $voucher->ticket_bundle->bundle_name == 2 ? 'VIP' : $voucher->ticket_bundle->bundle_name)) : trans('event.normal-ticket') }}
                                        </h5>
                                        <span class="text-detail event-description">
                                            {{ $ticket->event->description }}
                                            <br>
                                            {{ trans('event.starts') }} {{ $ticket->event->starts_at->diffForHumans() }}
                                        </span>

                                    </div>
                                    <div class="col-md-3">
                                        @if ($ticket->is_call)
                                            <br>
                                            <br>
                                            <a href="https://call.eventssmarter.com/rooms/{{ $voucher->id }}?{{ $login_token }}" target="_blank" class="btn btn-default btn-block">
                                                <i class="fa fa-phone" style="padding-right:5px"></i> {{ trans('event.access-call') }}
                                            </a>
                                        @else
                                            <a href="{{ route('voucher.preview', $voucher) }}" target="_blank" class="btn btn-default btn-block">
                                                <i class="fa fa-eye" style="padding-right:5px"></i> {{ trans('event.preview-ticket') }}
                                            </a>
                                            <a href="{{ route('voucher.download', $voucher) }}" target="_blank" class="btn btn-default btn-block">
                                                <i class="fa fa-download" style="padding-right:5px"></i> {{ trans('event.download-ticket') }}
                                            </a>
                                            @if ($voucher->downloaded_at)
                                                <small class="text-muted">
                                                    {{ trans('event.downloaded') }} {{ $voucher->downloaded_at->diffForHumans() }}
                                                </small>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
