@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.privacy-policy') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('content')
    <div class="container privacy">

        <a href="/privacy">
            <button class="btn btn-primary btn-privacy">{{ trans('general.privacy-policy') }}</button>
        </a>

        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h1>{{ trans('general.privacy-policy') }} (EU GDPR)</h1>
                        <br>
                        <p>{!! trans('general.privacy.privacy-17') !!}.</p>
                        <p class="privacy-title"><br />1. {{ trans('general.privacy.who-we-are') }}?</p>
                        <p>{!! trans('general.privacy.privacy-18') !!}.</p>
                        <p class="privacy-title"><br />2. {{ trans('general.privacy.what-personal-data-we-collect') }}?</p>
                        <p>{{ trans('general.privacy.privacy-19') }}:
                        <ul>
                            <li>
                                {{ trans('auth.name') }};
                            </li>
                            <li>
                                {{ trans('general.privacy.delivery-address-home-address') }};
                            </li>
                            <li>
                                {{ trans('checkout.phone-number') }};
                            </li>
                            <li>
                                {{ trans('checkout.email-address') }};
                            </li>
                            <li>
                                {{ trans('general.privacy.privacy-20') }}.
                            </li>
                        </ul>
                        </p>
                        <p>{!! trans('general.privacy.privacy-21') !!}</p>
                        <p class="privacy-title"><br />3. {{ trans('general.privacy.for-what-purposes-do-we-use-your-data') }}?</p>
                        <p>
                            {!! trans('general.privacy.privacy-22') !!}
                        </p>
                        <p class="privacy-title"><br />4. {{ trans('general.privacy.on-what-grounds-company-processes-your-personal-data') }}?</p>
                        <p>
                            {!! trans('general.privacy.privacy-23') !!}.
                        </p>
                        <p class="privacy-title"><br />5. {{ trans('general.privacy.natural-legal-personas-receiving-data') }}</p>
                        <p>
                            {!! trans('general.privacy.privacy-24') !!}
                        </p>

                        <p class="privacy-title"><br />6. {{ trans('general.privacy.your-personal-data-outside-europe-area') }}</p>
                        <p>{!! trans('general.privacy.privacy-25') !!}.</p>

                        <p class="privacy-title"><br />7. {{ trans('general.privacy.period-which-personal-data-stored') }}</p>
                        <p>
                            {!! trans('general.privacy.privacy-26') !!}
                        </p>
                        <p class="privacy-title"><br />8. {{ trans('general.privacy.how-company-protects-personal-data') }}?</p>
                        <p>{!! trans('general.privacy.privacy-27') !!}.</p>

                        <p class="privacy-title"><br />9. {{ trans('general.privacy.your-personal-data-protection-rights') }}</p>
                        <p>
                            {!! trans('general.privacy.privacy-28') !!}
                        </p>
                        <p class="privacy-title"><br />10. {{ trans('general.privacy.privacy-29') }}.</p>
                        <p class="privacy-title"><br />11. {{ trans('general.privacy.how-to-exercise-your-rights') }}?</p>
                        <p>
                            {!! trans('general.privacy.privacy-30') !!}
                        </p>
                        <p class="privacy-title"><br />12. {{ trans('general.privacy.privacy-policy-changes') }}</p>
                        <p>
                            {!! trans('general.privacy.privacy-31') !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
