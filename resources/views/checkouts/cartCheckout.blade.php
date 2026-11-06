@extends('layouts.app')

@section('meta')
    <title>{{ trans('checkout.checkout-cart') }} | {{ env('APP_NAME') }}</title>
    <style>
        @media only screen and (max-width: 989px) {
            .terms-input {
                margin-top: 10px;
            }
        }

        @media only screen and (min-width: 768px) {
            #card-element {
                margin-top: -30px;
            }
        }

        .bold {
            font-weight: bold;
            color: black;
        }

        .payment-option span {
            margin-right: 5px;
        }

        .payment-option span img {
            height: 22px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row line">
                            <div class="col-md-10">
                                {{ trans('checkout.you-are-buying-tickets-from') }} {{ env('APP_NAME') }}. <br>
                                {{ trans('checkout.your-tickets-are-guaranteed-by-the') }} {{ env('APP_NAME') }} {{ trans('checkout.buyer-guarantee') }}
                                <button type="button" class="btn-tooltip" data-toggle="tooltip" data-html="true" title="{{ trans('checkout.view-buyer-guarantee') }}">
                                    <a href="/terms" target="blank"><i class="fa fa-question-circle"></i></a>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <form action="/cart/charge" method="post" id="payment-form">

                            {{ csrf_field() }}

                            <cart-checkout :cart-items="{{ json_encode(array_values(\Cart::getContent()->toArray()), true) }}"
                                :i18n="{{ json_encode([
                                    'please_input_here' => trans('checkout.please-input-here'),
                                    'price' => trans('checkout.price'),
                                    'promocode' => trans('checkout.promocode'),
                                    'tax' => trans('checkout.tax'),
                                    'total' => trans('checkout.total'),
                                    'apply_promocode' => trans('checkout.apply-promocode'),
                                    'success' => trans('checkout.success'),
                                    'promocode_wrong' => trans('checkout.promocode-wrong'),
                                ]) }}">
                            </cart-checkout>

                            @if (!$user)
                                <div class="row line bold">
                                    <div class="col-md-3">
                                        {{ trans('checkout.contact-information') }}
                                    </div>
                                    <contact-info
                                        :i18n="{{ json_encode([
                                            'first_name' => trans('checkout.first-name'),
                                            'last_name' => trans('checkout.last-name'),
                                            'email_address' => trans('checkout.email-address'),
                                            'phone_number' => trans('checkout.phone-number'),
                                            'that_email_already_exists' => trans('checkout.that-email-already-eixsts'),
                                            'please_login' => trans('checkout.please-login'),
                                            'before_continuing' => trans('checkout.before-continuing'),
                                            'or_you_can_reset_password' => trans('checkout.or-you-can-reset-password'),
                                            'here' => trans('checkout.here'),
                                            'to_login' => trans('checkout.to-login'),
                                        ]) }}">
                                    </contact-info>
                                    <div class="col-md-9 col-md-offset-3">
                                        @if ($errors->has('email'))
                                            <div class="text-danger">{{ $errors->first('email') }}</div>
                                        @endif
                                        @if ($errors->has('fname'))
                                            <div class="text-danger">{{ $errors->first('fname') }}</div>
                                        @endif
                                        @if ($errors->has('lname'))
                                            <div class="text-danger">{{ $errors->first('lname') }}</div>
                                        @endif
                                        @if ($errors->has('phone_number'))
                                            <div class="text-danger">{{ $errors->first('phone_number') }}</div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="row" style="border-bottom: 1px solid gainsboro;margin-top:10px">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <input type="hidden" name="phone_number" value="{{ $user->phone }}">
                                </div>
                            @endif

                            <div class="row line">
                                <div class="col-md-2 bold">
                                    {{ trans('checkout.delivery-method') }}
                                </div>
                                <div class="col-md-10">
                                    {{ trans('checkout.delivery-method-des') }}
                                </div>
                            </div>
                            @if ($totalPrice > 0)
                                <div class="row line">
                                    <div class="col-md-2 col-sm-4 col-xs-4 bold">
                                        {{ trans('checkout.payment-method') }}
                                    </div>
                                    <div class="col-md-10 col-sm-6 col-xs-6 payment-option">
                                        <label style="cursor: pointer">
                                            <input checked type="radio" name="payment_method" value="card" style="margin-right:10px;">{{ trans('checkout.credit-debit-card') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="row line" style="padding-bottom: 0px;">
                                    <div class="col-md-2 col-sm-3 col-xs-12 bold">
                                        {{ trans('checkout.payment') }}
                                    </div>
                                    <div class="col-md-10 col-sm-7 col-xs-12" style="padding-top: 15px;">
                                        <div id="card-element">
                                        </div>
                                        <input type="hidden" name="nonce" id="nonce">
                                        @if ($errors->has('card'))
                                            <div class="text-danger">{{ $errors->first('card') }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="row line">
                                <div class="col-md-2 bold">
                                    {{ trans('checkout.promocode') }}
                                </div>
                                <div class="col-md-2 payment-option">
                                    <input name="promo_code" id="promo_code" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary blue" type="button" @click="emitter.emit('applyPromo')">{{ trans('checkout.apply') }}</button>
                                </div>
                            </div>

                            <br><br>

                            <div class="row">
                                <div class="col-md-1 col-xs-0"></div>
                                <div class="col-md-2 col-xs-6">
                                    <button type="button" class="btn btn-primary" id="pay-btn" style="background:#111">{{ trans('checkout.checkout') }}</button>
                                </div>
                                <div class="col-md-1 col-xs-6">
                                    <img src="{{ asset('images/flag.jpg') }}" alt="" style="width:100%; max-width: 50px">
                                </div>
                                <div class="col-md-6 col-xs-12 terms-input">
                                    <div class="checkbox" style="margin-top: -3px;">
                                        <label>
                                            <input type="hidden" name="agrees_to_terms" value="0" style="margin-top: 13px">
                                            <input type="checkbox" name="agrees_to_terms" value="1" style="margin-top: 13px" required>
                                            {{ trans('checkout.i-agree-to-the') }} {{ env('APP_NAME') }} <a href="/terms" target="_blank">
                                                {{ trans('checkout.terms-and-conditions') }}</a><br>
                                            {{ trans('checkout.all-sales-are-final') }}
                                        </label>
                                    </div>
                                    @if ($errors->has('agrees_to_terms'))
                                        <div class="text-danger">{{ $errors->first('agrees_to_terms') }}</div>
                                    @endif
                                </div>
                            </div>
                            <input type="hidden" name="adsid" id="adsid">
                            <br><br>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script type="module">
        window.addEventListener("pageshow", function(event) {
            var historyTraversal = event.persisted ||
                (typeof window.performance != "undefined" &&
                    window.performance.navigation.type === 2);
            if (historyTraversal) {
                // Handle page restore.
                window.location.reload();
            }
        });
        $('.btn-tooltip').tooltip();

        setTimeout(() => {
            $('#donation_amount').focus()
        }, 200);

        $(document).ready(function() {
            $('#adsid').val(localStorage.getItem('adsid'));
            $('#promo_code').val(localStorage.getItem('promo_code'));
        });

        //poynt payment

        const poyntCollect = document.createElement("script");
        const options = {
            style: {
                theme: "default",
            },
            iFrame: {
                width: "100%",
                height: "400px",
                border: "0px",
                borderRadius: "4px"
            },
            displayComponents: {
                showEndingPage: false,
                submitTokenButton: false,
                zipCode: true,
                labels: true,
                emailAddress: true,
            },
            customCss: {
                "input": {
                    "base": "color: black;", // font color
                    "errored": "color: maroon;",
                    "cardNumber": "border-bottom: 1px;",
                    "expiryDate": "text-transform: uppercase; color: green",
                    "cvc": "color: purple;"
                },
            },
            additionalFieldsToValidate: ["emailAddress"],
            @auth
            fields: {
                emailAddress: '{{ Auth::user()->email }}',
            },
            @endauth
        };
        poyntCollect.src = "https://poynt.net/snippet/poynt-collect/bundle.js";
        poyntCollect.async = true;
        poyntCollect.parentUrl = window.location.hostname;
        poyntCollect.onload = () => {
            const collect = new TokenizeJs(
                '{{ env('POYNT_BUSINESS_ID') }}',
                "{{ env('POYNT_APP_ID') }}",
            );
            collect.mount("card-element", document, options);
            var button = $('#pay-btn')[0];
            button.addEventListener("click", event => {
                var form = document.getElementById('payment-form')
                if (form.checkValidity() == true) {
                    $('#pay-btn').attr('disabled', 'disabled');
                    event.preventDefault();
                    @if ($totalPrice > 0)
                        collect.getNonce({});
                    @else
                        $('#payment-form').submit();
                    @endif
                    swal({
                        text: '{{ trans('checkout.processing-your-payment') }}...',
                        buttons: false,
                        closeOnClickOutside: false,
                        icon: "info"
                    });
                } else {
                    form.reportValidity();
                }
            });
            collect.on("error", event => {
                $('#pay-btn').removeAttr('disabled');
                swal.close();
            });
            collect.on("nonce", event => {
                $('#pay-btn').attr('disabled', 'disabled');
                $('#nonce').val(event.data.nonce);
                $('#payment-form').submit();
            });
        }
        document.body && document.body.appendChild(poyntCollect);
    </script>
@endsection
