@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.privacy-policy') }} | {{ env('APP_NAME') }}</title>
    <style>
        .privacy {
            color: black;
        }

        .privacy .privacy-title {
            line-height: 115%;
            font-family: Arial;
            font-size: 19px;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="container privacy">
        @if (session()->has('locale') && session()->get('locale') != 'en')
            <a href="/privacy-eu">
                <button class="btn btn-primary btn-privacy">{{ trans('general.privacy-policy') }} (EU GDPR)</button>
            </a>
        @endif
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h1>{{ trans('general.privacy-policy') }}</h1>
                        <p>{{ trans('general.privacy.privacy-1') }}.</p>
                        <p class="privacy-title"><br />1. {{ trans('general.privacy.collection-and-use-personal-information') }}</p>
                        <p>{{ trans('general.privacy.privacy-2') }}.</p>
                        <p class="privacy-title"><br />2. {{ trans('general.privacy.personal-information-we-collect') }}</p>
                        <p>{{ trans('general.privacy.privacy-3') }}.</p>
                        <p>{{ trans('general.privacy.privacy-4') }}.</p>
                        <p>{{ trans('general.privacy.privacy-5') }}.</p>
                        <p>{{ trans('general.privacy.privacy-6') }}.</p>
                        <p>{{ trans('general.privacy.information-collected-software-analytics') }}</p>
                        <p>{{ trans('general.privacy.privacy-7') }}.</p>
                        <p class="privacy-title"><br />3. {{ trans('general.privacy.cookies') }}</p>
                        <p>{{ trans('general.privacy.privacy-8') }}.</p>
                        <p class="privacy-title"><br />4. {{ trans('general.privacy.web-beacons') }}</p>
                        <p>{{ trans('general.privacy.privacy-8-1') }}.</p>
                        <p class="privacy-title"><br />5. {{ trans('general.privacy.data-storage') }}</p>
                        <p>{{ trans('general.privacy.privacy-9') }}.</p>
                        <p class="privacy-title"><br />6. {{ trans('general.privacy.disclosure-to-third-parties') }}</p>
                        <p>{{ trans('general.privacy.privacy-10') }}:</p>
                        <p>{!! trans('general.privacy.privacy-11') !!}</p>
                        <p class="privacy-title"><br />7. {{ trans('general.privacy.hosted-service-partners-privacy-options') }}</p>
                        <p>{{ trans('general.privacy.privacy-12') }}.</p>
                        <p class="privacy-title"><br />8. {{ trans('general.privacy.children') }}</p>
                        <p>{{ trans('general.privacy.privacy-13') }}.</p>
                        <p class="privacy-title"><br />9. {{ trans('general.privacy.tracking') }}</p>
                        <p>{{ trans('general.privacy.privacy-14') }}.</p>
                        <p class="privacy-title"><br />10. {{ trans('general.privacy.our-company-wide-commitment-privacy') }}</p>
                        <p>{{ trans('general.privacy.privacy-15') }}.</p>
                        <p class="privacy-title"><br />11. {{ trans('general.privacy.changes') }}</p>
                        <p>{{ trans('general.privacy.privacy-16') }}.</p>
                        <p>&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
