@extends('layouts.app')

@section('meta')
    <title>{{ trans('checkout.checkout-event') }} | {{ env('APP_NAME') }}</title>
    <style>
        @media only screen and (max-width: 989px) {
            .terms-input {
                margin-top: 10px;
            }
        }

        #donation_amount {
            border: 1px solid #bbb;
        }

        #donation_amount:focus {
            border-color: #dd2bd4;
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

        #agrees_to_states {
            margin-top: 6px;
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}/css/seatLayout.css">
    @if ($ticket->event->theatre_id && $ticket->event->theatre_json)
        <script src="https://code.jquery.com/jquery-1.12.4.min.js" type="text/javascript"></script>
        <script src="{{ env('APP_URL') }}/js/svg-inject.min.js" type="text/javascript"></script>
        <script src="{{ env('APP_URL') }}/js/seatBuyLayout.js" type="text/javascript"></script>
    @endif
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row text-center">
                            <div class="col-md-12">
                                <h3>{{ $ticket->event->name }} - {{ $ticket->event->organization->name }}</h3>
                                <h5>{{ $ticket->event->starts_at->format('l, F jS \a\t g:i:a') . ' - ' . $ticket->event->cityState }}</h5>
                            </div>
                        </div>
                        <br>
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
                        <form action="/charge" method="post" id="payment-form">

                            {{ csrf_field() }}

                            <input type="hidden" name="ticket" value="{{ $ticket->id }}">
                            <input type="hidden" name="stripe_token" id="stripe_token" value="false">
                            <input type="hidden" name="promo_code" id="promo_code">

                            @if ($ticket->is_live_stream)
                                <small>{{ trans('checkout.you-can-not-share-live-stream-url') }}</small>
                            @endif

                            @if (!$ticket->is_donation && !$ticket->is_call && !$ticket->is_reservation)
                                <?php
                                $currencies = \App\Models\Helpers::$currencies;
                                ?>
                                <ticket-purchase-form :old-bundles="{{ $bundles }}" :left-quantity="{{ $ticket->quantity }}" :seat="{{ $ticket->event->theatre_id && $ticket->event->theatre_json && $ticket->is_seat ? true : 0 }}" currency="{{ $ticket->event->organization->currency }}"
                                    :currencies="{{ json_encode($currencies) }}">
                                </ticket-purchase-form>
                            @elseif($ticket->is_donation)
                                <div class="row" style="margin-top: 30px;margin-bottom:30px">
                                    <div class="col-md-2">
                                        {{ trans('checkout.donation-amount') }}
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-addon">{{ $ticket->event->organization->currencySymbol }} </span>
                                            <input type="number" step="any" id="donation_amount" class="form-control" name="donation_amount" autofocus>
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="bundle_purchases[]" value="0">
                                        </div>
                                        <div class="text-danger" style="margin-top: 10px">{{ $errors->first('donation_warning') }}</div>
                                    </div>
                                </div>
                            @elseif($ticket->is_call)
                                <h4 style="text-align: center;margin-bottom:20px;color:#fb0;">{{ trans('checkout.please-choose-call-time') }}</h4>
                                <book-call-form event_start_date="{{ $ticket->event->starts_at }}" event_end_date="{{ $ticket->event->ends_at }}" avilable_weekends="{{ $ticket->call_available_weekends }}" call_avilable_start_time="{{ $ticket->call_available_start_time }}"
                                    call_avilable_end_time="{{ $ticket->call_available_end_time }}" call_time="{{ $ticket->call_time }}" :old_call_start_times="{{ json_encode($call_start_times) }}" price="{{ $ticket->price }}" call_time_warning="{{ $errors->first('call_ticket_warning') }}"
                                    quantity="{{ $ticket->quantity }}"></book-call-form>
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="bundle_purchases[]" value="0">
                            @elseif($ticket->is_reservation)
                                <h4 style="text-align: center;margin-bottom:20px;color:#fb0;">{{ trans('checkout.please-choose-reservation-time') }}</h4>
                                <reservation-form event_start_date="{{ $ticket->event->starts_at }}" event_end_date="{{ $ticket->event->ends_at }}" avilable_weekends="{{ $ticket->reservation_available_weekends }}" reservation_avilable_start_time="{{ $ticket->reservation_available_start_time }}"
                                    reservation_avilable_end_time="{{ $ticket->reservation_available_end_time }}" reservation_time="{{ $ticket->reservation_time }}" :old_reservation_start_times="{{ json_encode($reservation_start_times) }}" price="{{ $ticket->price }}"
                                    reservation_time_warning="{{ $errors->first('reservation_ticket_warning') }}" quantity="{{ $ticket->quantity }}"></reservation-form>
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="bundle_purchases[]" value="0">
                            @endif
                            @if ($errors->has('seat'))
                                <div class="text-danger" style="margin-top: 20px">{{ $errors->first('seat') }}</div>
                            @endif
                            @if ($ticket->event->theatre_id && $ticket->event->theatre_json && $ticket->is_seat)
                                @if ($ticket->event->theatre->svg_path)
                                    <h4 class="text-center" style="margin-bottom: 40px"> -- {{ trans('checkout.choose-section-and-seats') }} ({{ $ticket->event->theatre->name }}) --</h4>
                                    <div class="row" id="seatBox">
                                        <div class="col-md-6">
                                            <img src="{{ $ticket->event->theatre->svg_path }}" onload="SVGInject(this)" class="stadium-svg" />
                                        </div>
                                        <div class="col-md-6">
                                            <div id="seatMap">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <h4 class="text-center"> -- {{ trans('checkout.choose-seats') }} ({{ $ticket->event->theatre->name }}) --</h4>
                                    <div id="seatMap" style="overflow: auto"></div>
                                @endif
                                <input type="hidden" name="selectedSeats" id="selectedSeats">
                            @endif

                            @if ($errors->has('quantity'))
                                <div class="text-danger">{{ $errors->first('quantity') }}</div>
                            @endif

                            @if ($ticket->event->prompt)
                                <div class="form-group">
                                    <p>
                                        <strong>{{ $ticket->event->prompt }}</strong>
                                    </p>
                                </div>
                            @endif

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
                            @if ($ticket->cents !== 0)
                                <div class="row line">
                                    <div class="col-md-2 bold">
                                        {{ trans('checkout.payment-method') }}
                                    </div>
                                    <div class="col-md-10 payment-option">
                                        <label style="cursor: pointer">
                                            <input {{ $ticket->cents === 0 && !$ticket->is_donation ? 'disabled' : 'checked' }} type="radio" name="payment_method" value="card" style="margin-right:10px;">{{ trans('checkout.credit-debit-card') }}
                                        </label>
                                        @if ($ticket->event->organization->is_crypto && $ticket->event->organization->wallet_address)
                                            <br />
                                            <label style="cursor: pointer;margin-top:5px;">
                                                <input type="radio" name="payment_method" value="crypto" style="margin-right:10px;">
                                                <span class="paypal">
                                                    <img src="/images/crypto/etherum.svg">
                                                </span>
                                                {{ trans('checkout.crypto') }}
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($ticket->cents !== 0)
                                <div class="row line">
                                    <div class="col-md-2 bold">
                                        {{ trans('checkout.promocode') }}
                                    </div>
                                    <div class="col-md-2 payment-option">
                                        <input name="code" id="code" class="form-control">
                                    </div>
                                </div>
                            @endif

                            @if ($ticket->event->organization->category == 'Political')
                                <div class="row line">
                                    <div class="col-md-2 bold">
                                        {{ trans('checkout.personal-information') }}
                                    </div>
                                    <div class="col-md-10">

                                        <div class="form-group">
                                            <label>{{ trans('checkout.address') }} </label>
                                            <input type="text" name="address" id="address" placeholder="{{ trans('checkout.address') }}" class="form-control" required>
                                            @if ($errors->has('address'))
                                                <div class="text-danger">{{ $errors->first('address') }}</div>
                                            @endif

                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('checkout.employer') }} </label>
                                            <input type="text" name="employer" id="employer" placeholder="{{ trans('checkout.employer') }}" class="form-control" required>
                                            @if ($errors->has('employer'))
                                                <div class="text-danger">{{ $errors->first('employer') }}</div>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label>{{ trans('checkout.occupation') }} </label>
                                            <input type="text" name="occupation" id="occupation" placeholder="{{ trans('checkout.occupation') }}" class="form-control" required>
                                            @if ($errors->has('occupation'))
                                                <div class="text-danger">{{ $errors->first('occupation') }}</div>
                                            @endif
                                        </div>
                                        @if ($ticket->event->statement)
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="agrees_to_states">
                                                        <input type="checkbox" name="agrees_to_states" id="agrees_to_states" required>
                                                        {{ trans('checkout.i-agree-following-statements') }}
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div style="max-height: 120px; overflow-y:auto; padding: 3px; border: 1px solid #e6ebf1; border-radius: 5px">
                                                    <p>
                                                        {{ $ticket->event->statement }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <br><br>

                            <div class="row">
                                <div class="col-md-1 col-xs-0"></div>
                                <div class="col-md-2 col-xs-6">
                                    <button class="btn btn-primary" id="pay-btn" style="background:#111">{{ trans('checkout.checkout') }}</button>
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
    <script type="module">
        window.addEventListener( "pageshow", function ( event ) {
            var historyTraversal = event.persisted ||
                                    ( typeof window.performance != "undefined" &&
                                        window.performance.navigation.type === 2 );
            if ( historyTraversal ) {
                // Handle page restore.
                window.location.reload();
            }
        });
        $('.btn-tooltip').tooltip();

        setTimeout(() => {
            $('#donation_amount').focus()
        }, 200);

        $('form').submit(() => {
            setTimeout(() => {
                $('#pay-btn').prop('disabled', true);
            }, 300);
        });

        $(document).ready(function(){
            $('#adsid').val(localStorage.getItem('adsid'));
            $('#promo_code').val(localStorage.getItem('promo_code'));
        });
    </script>
    @if ($ticket->event->theatre_id && $ticket->event->theatre_json)
        <script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
        <script src="{{ env('APP_URL') }}/js/seatBuyLayout.js" defer></script>
        <script type="module">
            var initSeatCount = parseInt($('#purchase_quantity').val());
            if ('{{ $ticket->event->theatre->svg_path }}'){
                var selectedId = null
                var sectionsArray = @json($ticket->event->theatre_json).colAreas.objArea;
                SVGInject.setOptions({
                    makeIdsUnique: false,
                    useCache: false,
                    beforeInject: function(img, svg) {
                        svg.classList.add('stadium-svg');
                    },
                });
                $(document).ready(() =>{
                    setTimeout(() => {
                        sectionsArray.map(section =>{
                            let rows = []
                            section.objRow.map(row =>{
                                if (row.rowStatus && row.ticket_id === {{ $ticket->id }}){
                                    rows.push(row.PhyRowId)
                                }
                            })
                            if (rows.length > 0){
                                $('#' + section.AreaDesc).addClass('available')
                            }
                        })
                    }, 1000);
                })
                $(document).on({
                    mouseenter: function (e) {
                        if ($('#' + e.target.id).hasClass('available')){
                            $('#' + e.target.id).css({'fill':'#ACCEF7'})
                        }
                        else $('#' + e.target.id).css({'fill':'#ddd'})
                    },
                    mouseleave: function (e) {
                        if (selectedId !== e.target.id){
                            $('#' + e.target.id).css({'fill':''})
                        }
                    }
                }, ".stadium-svg>g");
                $(document).on('click','.stadium-svg path',function(e){
                    if(e.target.id){
                        if ($('#' + e.target.id).hasClass('available')){
                            if (selectedId != e.target.id){
                                if (selectedId) $('#' + selectedId).css({'fill':''})
                                selectedId = e.target.id;
                            }
                            $('#' + e.target.id).css({'fill':'#ACCEF7'})
                            let seatLayout = @json($ticket->event->theatre_json);
                            sectionsArray.some(section =>{
                                if (section.AreaDesc === selectedId){
                                    seatLayout.colAreas.objArea = [section]
                                    seatLayout.colAreas.intMaxSeatId = section.intMaxSeatId - 1
                                    return true
                                }
                            })

                            var seatData = {
                                "theater_id" : '{{ $ticket->event->theatre_id }}',
                                seatLayout
                            }
                            $('#seatMap').seatLayout({
                                data: seatData,
                                showActionButtons:false,
                                numberOfSeat: initSeatCount,
                                ticket_id: {{ $ticket->id }},
                                callOnSeatSelect: function (_event, _data, element) {
                                    $('#selectedSeats').val(JSON.stringify(_data.selected))
                                },
                                stageShow: false
                            });
                        }
                    }
                });
                function initSeat(numberOfSeat){
                    initSeatCount = numberOfSeat
                    if (!selectedId){
                        return;
                    }
                    let seatLayout = @json($ticket->event->theatre_json);
                    sectionsArray.some(section =>{
                        if (section.AreaDesc === selectedId){
                            seatLayout.colAreas.objArea = [section]
                            seatLayout.colAreas.intMaxSeatId = section.intMaxSeatId - 1
                            return true
                        }
                    })

                    var seatData = {
                        "theater_id" : '{{ $ticket->event->theatre_id }}',
                        seatLayout
                    }
                    $('#seatMap').seatLayout({
                        data: seatData,
                        showActionButtons:false,
                        numberOfSeat: numberOfSeat,
                        ticket_id: {{ $ticket->id }},
                        callOnSeatSelect: function (_event, _data, element) {
                            $('#selectedSeats').val(JSON.stringify(_data.selected))
                        },
                        stageShow: false
                    });
                }
            }
            else {
                var seatData = {
                    "theater_id" : '{{ $ticket->event->theatre_id }}',
                    "seatLayout" : @json($ticket->event->theatre_json)
                }
                function initSeat(numberOfSeat){
                    $('#seatMap').seatLayout({
                        data: seatData,
                        showActionButtons:false,
                        numberOfSeat: numberOfSeat,
                        ticket_id: {{ $ticket->id }},
                        callOnSeatSelect: function (_event, _data, element) {
                            $('#selectedSeats').val(JSON.stringify(_data.selected))
                        }
                    });
                }
                if (initSeatCount){
                    initSeat(initSeatCount);
                }
                else initSeat(1);
            }

            $(".btn-minus").click(function() {
                var seatCount = parseInt($('#purchase_quantity').val())
                initSeat(seatCount);
                return false
            });
            $(".btn-plus").click(function() {
                var seatCount = parseInt($('#purchase_quantity').val())
                initSeat(seatCount);
                return false
            });
        </script>
    @endif
@endsection
