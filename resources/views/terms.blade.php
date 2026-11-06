@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.terms.terms-use') }} | {{ env('APP_NAME') }}</title>
    <style>
        .terms {
            color: black;
        }

        .bold {
            font-weight: bold;
        }

        .terms p {
            font-family: Arial;
        }

        .terms h2 {
            margin-bottom: 10px;
        }

        .terms h3 {
            line-height: 115%;
            font-family: Arial;
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .terms h4 {
            font-family: Arial;
            font-size: 15px;
            font-weight: bold;
        }

        .terms .contact li {
            font-size: 17px;
        }
    </style>
@endsection

@section('content')
    <div class="container terms">

        <div class="row">

            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h2>{{ strtoupper(trans('general.terms.terms-use')) }}</h2>
                        <p>{{ trans('general.terms.last-updated') }} April 06, 2020</p>
                        <br>
                        <h3>{{ trans('general.terms.agreement-to-terms') }}</h3>
                        <p>{{ trans('general.terms.terms-1') }}</p>
                        <p>{{ trans('general.terms.terms-2') }}</p>
                        <p>{{ trans('general.terms.terms-3') }}</p>
                        <p>{{ trans('general.terms.terms-4') }}</p>
                        <p>{{ trans('general.terms.terms-5') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.intellectual-property-rights') }}</h3>
                        <p>{{ trans('general.terms.terms-6') }}</p>
                        <p>{{ trans('general.terms.terms-7') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.user-representations') }}</h3>
                        <p>{{ trans('general.terms.terms-8') }}</p>
                        <p>{{ trans('general.terms.terms-9') }}</p>
                        <p>{{ trans('general.terms.terms-10') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.user-registration') }}</h3>
                        <p>{{ trans('general.terms.terms-11') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.marketplace-offerings') }}</h3>
                        <p>{{ trans('general.terms.terms-12') }}</p>
                        <p>{{ trans('general.terms.terms-13') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.purchase-payment') }}</h3>
                        <p>{{ trans('general.terms.we-accept-the-following-payments') }}:</p>
                        <ul style="list-style: none;">
                            <li>- {{ trans('general.terms.visa') }}</li>
                            <li>- {{ trans('general.terms.mastercard') }}</li>
                            <li>- {{ trans('general.terms.american-express') }}</li>
                            <li>- {{ trans('general.terms.discover') }}</li>
                        </ul>
                        <p>{{ trans('general.terms.terms-14') }}</p>
                        <p>{{ trans('general.terms.terms-15') }}</p>
                        <p>{{ trans('general.terms.terms-16') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.return-refunds-policy') }}</h3>
                        <p>{{ trans('checkout.all-sales-are-final') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.payouts-reserves') }}</h3>
                        <p>{{ trans('general.terms.terms-17') }}</p>
                        <br>
                        <h3>{{ trans('general.terms.prohibited-activities') }}</h3>
                        <p>{{ trans('general.terms.terms-18') }}</p>
                        <p>{{ trans('general.terms.terms-19') }}:</p>
                        <ol>
                            <li>{{ trans('general.terms.terms-20') }}.</li>
                            <li>{{ trans('general.terms.terms-21') }}.</li>
                            <li>{{ trans('general.terms.terms-22') }}.</li>
                            <li>{{ trans('general.terms.terms-23') }}.</li>
                            <li>{{ trans('general.terms.terms-24') }}.</li>
                            <li>{{ trans('general.terms.terms-25') }}.</li>
                            <li>{{ trans('general.terms.terms-26') }}.</li>
                            <li>{{ trans('general.terms.terms-27') }}.</li>
                        </ol>
                        <br>
                        <h3>{{ trans('general.terms.user-generated-contributions') }}</h3>
                        <p>{{ trans('general.terms.terms-28') }}:</p>
                        <ol>
                            <li>{{ trans('general.terms.terms-29') }}.</li>
                            <li>{{ trans('general.terms.terms-30') }}.</li>
                            <li>{{ trans('general.terms.terms-31') }}.</li>
                            <li>{{ trans('general.terms.terms-32') }}.</li>
                            <li>{{ trans('general.terms.terms-33') }}.</li>
                            <li>{{ trans('general.terms.terms-34') }}.</li>
                            <li>{{ trans('general.terms.terms-35') }}.</li>
                            <li>{{ trans('general.terms.terms-36') }}.</li>
                            <li>{{ trans('general.terms.terms-37') }}.</li>
                            <li>{{ trans('general.terms.terms-38') }}.</li>
                            <li>{{ trans('general.terms.terms-39') }}.</li>
                            <li>{{ trans('general.terms.terms-40') }}.</li>
                            <li>{{ trans('general.terms.terms-41') }}.</li>
                            <li>{{ trans('general.terms.terms-42') }}.</li>
                        </ol>
                        <p>{{ trans('general.terms.terms-43') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.contribution-license') }}</h3>
                        <p>{{ trans('general.terms.terms-44') }}.</p>
                        <p>{{ trans('general.terms.terms-45') }}.</p>
                        <p>{{ trans('general.terms.terms-46') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.mobile-application-license') }}</h3>
                        <h4>{{ trans('general.terms.use-license') }}</h4>
                        <p>{{ trans('general.terms.terms-47') }}.</p>
                        <h4>{{ trans('general.terms.apple-and-android-devices') }}</h4>
                        <p>{{ trans('general.terms.terms-48') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.social-media') }}</h3>
                        <p>{{ trans('general.terms.terms-49') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.submissions') }}</h3>
                        <p>{{ trans('general.terms.terms-50') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.advertisers') }}</h3>
                        <p>{{ trans('general.terms.terms-51') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.site-management') }}</h3>
                        <p>{{ trans('general.terms.terms-52') }}.</p>
                        <br>
                        <h3>{{ trans('general.privacy-policy') }}</h3>
                        <p>{{ trans('general.terms.terms-53') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.term-and-termination') }}</h3>
                        <p>{{ trans('general.terms.terms-54') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.modifications-interruptions') }}</h3>
                        <p>{{ trans('general.terms.terms-55') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.governing-law') }}</h3>
                        <p>{{ trans('general.terms.terms-56') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.dispute-resolution') }}</h3>
                        <p>{{ trans('general.terms.terms-57') }}.</p>
                        <h4>{{ trans('general.terms.corrections') }}</h4>
                        <p>{{ trans('general.terms.terms-58') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.disclaimer') }}</h3>
                        <p>{{ trans('general.terms.terms-59') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.limitations-liability') }}</h3>
                        <p>{{ trans('general.terms.terms-60') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.indemnification') }}</h3>
                        <p>{{ trans('general.terms.terms-61') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.user-data') }}</h3>
                        <p>{{ trans('general.terms.terms-62') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.terms-63') }}</h3>
                        <p>{{ trans('general.terms.terms-64') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.deliver-shipping') }}</h3>
                        <p>{{ trans('general.terms.terms-65') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.california-users-residents') }}</h3>
                        <p>{{ trans('general.terms.terms-66') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.miscellaneous') }}</h3>
                        <p>{{ trans('general.terms.terms-67') }}.</p>
                        <p>{{ trans('general.terms.terms-68') }}.</p>
                        <br>
                        <h3>{{ trans('general.terms.contact-us') }}</h3>
                        <p>{{ trans('general.terms.terms-69') }}:</p>
                        <ul class="contact" style="list-style: none; padding: 0px;">
                            <li class="bold">Athletic Junction LLC</li>
                            <li class="bold">255 S Orange Avenue Suite 104 PMB1058</li>
                            <li class="bold">Orlando, FL 32801</li>
                            <li class="bold">United States</li>
                            <li class="bold">mvp@eventssmarter.com</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
