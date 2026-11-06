@extends('layouts.app')

@section('meta')
    <title>{{ trans('event.tickets') }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="View your tickets online with {{ env('APP_NAME') }}">
    <meta name="keywords" content="tickets">
    <meta property="og:title" content="View Your Tickets" />
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
    <meta property="og:description" content="View all the tickets you've purchased" />
@endsection

@section('page-title')
    {{ trans('event.view-your-tickets') }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('partials.my_upcoming_events')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script type="module">
        if (<?php echo $emailSent; ?>) {
            swal({
                text: "{{ trans('event.your-tickets-have-also-been-sent-to') }} <?php echo $user->email; ?> {!! trans('event.check-spam-email-confirmation-we-made-qr-code') !!}",
                icon: "success"
            });
        }
    </script>
@endsection
