@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.footer.contact') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('content')
    <div class="container">

        <div class="row">

            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h2 style="margin-bottom: 20px;">{{ trans('general.footer.contact') }}</h2>
                        <ul class="fusion-checklist fusion-checklist-1" style="font-size:15px;line-height:25.5px;">
                            <li class="fusion-li-item" style="display: flex;align-items: center;">
                                <i class="fa fa-map-marker" style="font-size: 16px;padding-left:3px;"></i>
                                <div class="fusion-li-item-content" style="margin-left:36px;">
                                    <p style="margin: 0px;"><strong>{{ trans('checkout.address') }}</strong>: 255 S Orange Avenue Suite 104 PMB1058 Orlando, FL 32801</p>
                                </div>
                            </li>
                            <li class="fusion-li-item" style="display: flex;align-items: center;">
                                <i class="fa fa-envelope" style="font-size: 16px;"></i>
                                <div class="fusion-li-item-content" style="margin-left:31px;"><strong>{{ trans('auth.email') }}</strong>: <a href="mailto:mvp@eventssmarter.com">mvp@eventssmarter.com</a></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
