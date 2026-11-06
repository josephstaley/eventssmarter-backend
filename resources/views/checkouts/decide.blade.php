@extends('layouts.app')

@section('page-title')
    {{ trans('general.menu.purchase-tickets') }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="row text-center">
                    <div class="col-sm-6">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading">&nbsp;</div>
                            <div class="panel-body">
                                <div class="decide"><i class="fa fa-fw fa-5x fa-ticket"></i></div>
                                <a id="guest_checkout" href="{{ route('checkout.create', ['id' => $ticket->id, 'quantity' => $quantity, 'type' => 'guest']) }}" class="btn small-section btn-primary btn-lg">{{ trans('checkout.checkout-as-guest') }}
                                    <br><small>{{ trans('checkout.takes-10-seconds') }}</small></a>
                                <div class="decide-detail"><i class="fa fa-check"></i> {{ trans('checkout.we-will-send-tickets-your-email') }}.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="panel panel-default text-center">
                            <div class="panel-heading">&nbsp;</div>
                            <div class="panel-body">
                                <div class="decide user"><i class="fa fa-fw fa-5x fa-user"></i></div>
                                <a href="{{ route('register') }}" class="btn small-section btn-signup btn-lg">{{ trans('checkout.create-account') }} <br><small>{{ trans('checkout.takes-30-seconds') }}</small></a>
                                <div class="decide-detail">
                                    <p><i class="fa fa-check"></i> {{ trans('checkout.keep-view-your-tickets') }}.</p>
                                    <p><i class="fa fa-check"></i> {{ trans('checkout.follow-schools-organizations') }}.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
