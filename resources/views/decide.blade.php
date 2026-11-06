@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.buy-or-sell') }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="Buy or Sell Tickets using Online using {{ env('APP_NAME') }}" />
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
@endsection

@section('content')
    <div class="container" style="margin-bottom: 100px">

        <div class="row">

            <div class="col-sm-6">
                <div class="panel panel-default text-center">
                    <div class="panel-heading">&nbsp;</div>
                    <div class="panel-body">
                        <div class="decide"><i class="fa fa-fw fa-5x fa-ticket"></i></div>
                        <a href="{{ route('register') }}?sell_tickets=true" class="btn btn-primary btn-lg">{{ trans('general.sign-up-to-sell-tickets') }}</a>
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="panel panel-default text-center">
                    <div class="panel-heading">&nbsp;</div>
                    <div class="panel-body">
                        <div class="decide user"><i class="fa fa-fw fa-5x fa-user"></i></div>
                        <a href="{{ route('register') }}" class="btn btn-signup btn-lg">{{ trans('general.sign-up-to-buy-tickets') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
