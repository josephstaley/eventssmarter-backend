@extends('layouts.app')

@section('meta')
    <link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}/css/seatLayout.css">
    @if ($event->theatre_id && $event->theatre_json)
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" type="text/javascript"></script>
    <script src="{{ env('APP_URL') }}/js/seatPriceLayout.js" type="text/javascript"></script>
    <script src="{{ env('APP_URL') }}/js/svg-inject.min.js" type="text/javascript"></script>
    @endif
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.edit-ticket-for') }} {{ $ticket->event->name }} @ {{ $organization->name }}
                    </div>

                    <div class="panel-body">

                        <form method="post" action="{{ route('tickets.update', [$organization, $ticket->id]) }}">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name">{{ trans('event.ticket-name') }}</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') ? old('name') : $ticket->name }}" placeholder="{{ trans('auth.name') }}" required>
                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                                <label for="name">{{ trans('event.ticket-description') }}</label>
                               <textarea class="form-control" name="description" rows="6">{{ old('description') ? old('description') : $ticket->description }}</textarea>
                                @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('quantity') ? ' has-error' : '' }}" id="quantity-box">
                                <label for="name">{{ trans('checkout.quantity') }}</label>
                                <input type="text" class="form-control" id="quantity" name="quantity" value="{{ old('quantity') ? old('quantity') : ($ticket->quantity ? $ticket->quantity : 0) }}" placeholder="{{ trans('checkout.quantity') }}" required>
                                @if ($errors->has('quantity'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('quantity') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('price') ? ' has-error' : '' }}">

                                @if(Session::has('bundle-name-error'))
                                <div class="form-group has-error">
                                    <span class="help-block">
                                        <strong>{{ trans('event.bundle-name-empty') }}</strong>
                                    </span>
                                </div>
                                @endif
                                <?php
                                    $currencies = \App\Models\Helpers::$currencies;
                                ?>
                                <ticket-price-calculator
                                        :old="{{ ($old=old('price')) ? intval($old)/100 : $ticket->originPrice }}"
                                        :app-fee-percentage="{{ $organization->app_fee_percentage }}"
                                        :old-bundles="{{ $ticket->bundles }}"
                                        :has-affiliate="{{ $ticket->has_affiliate }}"
                                        :old-affiliate-email="'{{ $ticket->has_affiliate == 1 ? $ticket->affiliate_email : 1 }}'"
                                        :old-affiliate-amount="{{ $ticket->has_affiliate == 1 ? $ticket->affiliate_amount : 1 }}"
                                        :old-is-live-stream="{{ $ticket->is_live_stream == 1 ? $ticket->is_live_stream : 0}}"
                                        :old-is-private="{{ $ticket->is_private == 1 ? $ticket->is_private : 0}}"
                                        :old-stream-url="'{{ $ticket->stream_url }}'"
                                        :old-is-donation="'{{ $ticket->is_donation }}'"
                                        :old-is-call="{{ $ticket->is_call == 1 ? $ticket->is_call : 0}}"
                                        old-call-available-start-time="{{ $ticket->call_available_start_time }}"
                                        old-call-available-end-time="{{ $ticket->call_available_end_time}}"
                                        :old-call-available-weekends="{{ $ticket->call_available_weekends == 1 ? $ticket->call_available_weekends : 0}}"
                                        :old-is-seat="{{ $ticket->is_seat == 1 ? $ticket->is_seat : 0}}"
                                        :seat="{{ $event->theatre_id && $event->theatre_json ? 1 : 0 }}"
                                        old-call-time="{{ $ticket->call_time? $ticket->call_time : 0 }}"
                                        :old-is-reservation="{{ $ticket->is_reservation == 1 ? $ticket->is_reservation : 0}}"
                                        old-reservation-available-start-time="{{ $ticket->reservation_available_start_time }}"
                                        old-reservation-available-end-time="{{ $ticket->reservation_available_end_time}}"
                                        :old-reservation-available-weekends="{{ $ticket->reservation_available_weekends == 1 ? $ticket->reservation_available_weekends : 0}}"
                                        old-reservation-time="{{ $ticket->reservation_time? $ticket->reservation_time : 0 }}"
                                        currency="{{ $event->organization->currency }}"
                                        :currencies="{{ json_encode($currencies) }}"
                                        :old_is_subscribe="{{ $ticket->is_subscribe }}"
                                        :old_discount_percent="{{ $ticket->discount_percent }}"
                                        :i18n="{{ json_encode([
                                            'ticket_price' => trans('event.ticket-price'),
                                            'price_per_ticket' => trans('event.price-per-ticket'),
                                            'you_make' => trans('event.you-make'),
                                            'bundle_name' => trans('event.bundle-name'),
                                            'count' => trans('event.count'),
                                            'price' => trans('checkout.price'),
                                            'add_bundle' => trans('event.add-bundle'),
                                            'subscribe' => trans('event.subscribe'),
                                            'discount_percent_for_subscribers' => trans('event.discount-percent-for-subscribers'),
                                            'discount_100percent_free' => trans('event.discount-100percent-free'),
                                            'donation' => trans('event.donation'),
                                            'live_stream' => trans('general.live-stream'),
                                            'eventssmarter_stream' => trans('event.eventssmarter-stream'),
                                            'twitch_stream' => trans('event.twitch-stream'),
                                            'other_stream' => trans('event.other-stream'),
                                            'private' => trans('event.private'),
                                            'livestream_url' => trans('event.livestream-url'),
                                            'twitch_channel_name' => trans('event.twitch-channel-name'),
                                            'call' => trans('event.call'),
                                            'call_available_time' => trans('event.call-available-time'),
                                            'to' => trans('event.to'),
                                            'available_weekends' => trans('event.available-weekends'),
                                            'call_duration_time' => trans('event.call-duration-time'),
                                            'min' => trans('event.min'),
                                            'hour' => trans('event.hour'),
                                            'reservation' => trans('event.reservation'),
                                            'reservation_available_time' => trans('event.reservation-available-time'),
                                            'reservation_duration_time' => trans('event.reservation-duration-time'),
                                            'seat' => trans('event.seat')
                                        ]) }}"
                                ></ticket-price-calculator>

                                @if ($errors->has('price'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('price') }}</strong>
                                    </span>
                                @endif
                                @if ($errors->has('affiliate_email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('affiliate_email') }}</strong>
                                    </span>
                                @endif
                                @if ($errors->has('affiliate_amount'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('affiliate_amount') }}</strong>
                                    </span>
                                @endif
                            </div>

                            @if ($event->theatre_id && $event->theatre_json)
                                @if ($event->theatre->svg_path)
                                <div class="row" id="seatBox">
                                    <div class="col-md-6">
                                        <img src="{{ $event->theatre->svg_path }}" srcset="{{ $event->theatre->svg_path }}" onload="SVGInject(this)" class="stadium-svg"/>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="text-center"> -- Choose row for seting price --</h4>
                                        <div id="sectionMap">
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="selectedSections" id="selectedSections">
                                @else
                                <div id="seatBox">
                                    <h4 class="text-center"> -- Choose seats for seting price ({{ $event->theatre->name }}) --</h4>
                                    <div id="seatMap" style="overflow: auto"></div>
                                    <input type="hidden" name="addedSeats" id="addedSeats">
                                    <input type="hidden" name="removedSeats" id="removedSeats">
                                </div>
                                @endif
                            @endif

                            <hr />

                            <div class="form-group">
                                <br />
                                <button type="submit" class="btn btn-primary pull-right" id="submit-btn">{{ trans('organization.update') }}</button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.delete-ticket') }}
                    </div>

                    <div class="panel-body">
                        <a href="{{ route('tickets.destroy', $ticket->id) }}" class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this ticket?')">{{ trans('banner.delete') }}</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="module">
    $('form').submit(() => {
        $("#submit-btn").attr("disabled", true);
    });
</script>
@if ($event->theatre_id && $event->theatre_json)
<script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
<script src="{{ env('APP_URL') }}/js/seatPriceLayout.js" defer></script>
<script src="{{ env('APP_URL') }}/js/svg-inject.min.js" type="text/javascript" defer></script>
<script type="module">
    if ('{{ $event->theatre->svg_path }}'){
        SVGInject.setOptions({
            makeIdsUnique: false,
            useCache: false,
            beforeInject: function(img, svg) {
                svg.classList.add('stadium-svg');
            }
        });
        var selectedSections = []; // [ {sectionName: "F1", sectionRows: ["1", "2", ...], sectionNum}, ... ]
        var targetIds = [];
        var sectionsArray = @json($event->theatre_json).colAreas.objArea;
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
                        selectedSections.push({
                            sectionName: section.AreaDesc,
                            sectionNum: section.AreaNum,
                            sectionRows: rows
                        })
                        $('#' + section.AreaDesc).css({'fill':'#ddd'})
                        targetIds.push(section.AreaDesc)
                    }
                })
                $('#selectedSections').val(JSON.stringify(selectedSections))
            }, 1000);
        })
        $(document).on('click','.stadium-svg path',function(e){
            if(e.target.id){
                let clickedId = e.target.id;
                if (clickedId.indexOf('--inject') !== -1){
                    clickedId = clickedId.substr(0, clickedId.indexOf('--inject'))
                }
                if (targetIds.indexOf(e.target.id) === -1){
                    targetIds.push(e.target.id)
                    $('#' + e.target.id).css({'fill':'#ddd'})
                    $('#sectionMap').html('')
                    sectionsArray.some(section =>{
                        if (section.AreaDesc === clickedId){
                            var index = null;
                            var rowsHtml = ''
                            if (selectedSections.some((item, idx) =>{
                                if (item.sectionName === clickedId){
                                    index = idx
                                    return true
                                }
                            })){
                                section.objRow.map(row =>{
                                    if (row.rowStatus && row.ticket_id !== {{ $ticket->id }}){
                                        rowsHtml += '<span class="disabled">' + row.PhyRowId + '</span>'
                                    }
                                    else if (selectedSections[index].sectionRows.some(rowNum =>{
                                        if (rowNum === row.PhyRowId) return true
                                    })){
                                        rowsHtml += '<span class="selected" onClick="clickRow(this, `' + section.AreaDesc + '`, `' + row.PhyRowId + '`)">' + row.PhyRowId + '</span>'
                                    }
                                    else rowsHtml += '<span onClick="clickRow(this, `' + section.AreaDesc + '`, `' + row.PhyRowId + '`)">' + row.PhyRowId + '</span>'
                                });
                            }
                            else {
                                let rows = []
                                section.objRow.map(row =>{
                                    if (row.rowStatus && row.ticket_id !== {{ $ticket->id }}){
                                        rowsHtml += '<span class="disabled">' + row.PhyRowId + '</span>'
                                    }
                                    else {
                                        rows.push(row.PhyRowId)
                                        rowsHtml += '<span class="selected" onClick="clickRow(this, `' + section.AreaDesc + '`, `' + row.PhyRowId + '`)">' + row.PhyRowId + '</span>'
                                    }
                                })
                                if (rows.length > 0)
                                selectedSections.push(
                                    {
                                        sectionName: section.AreaDesc,
                                        sectionNum: section.AreaNum,
                                        sectionRows: rows
                                    }
                                )
                            }
                            $('#sectionMap').html(rowsHtml)
                            return true;
                        }
                    })
                }
                else {
                    targetIds.splice(targetIds.indexOf(e.target.id), 1)
                    $('#' + e.target.id).css({'fill':''})
                    selectedSections.some((section, index) =>{
                        if (section.sectionName === clickedId){
                            delete selectedSections[index]
                            return true
                        }
                    })
                    $('#sectionMap').html('')
                }
                $('#selectedSections').val(JSON.stringify(selectedSections))
            }
        });
        $(document).on({
            mouseenter: function (e) {
                $('#' + e.target.id).css({'fill':'#ddd'})
            },
            mouseleave: function (e) {
                if (targetIds.indexOf(e.target.id) === -1){
                    $('#' + e.target.id).css({'fill':''})
                }
            }
        }, ".stadium-svg>g");
    }
    else {
        var seatData = {
            "theater_id" : '{{ $event->theatre_id }}',
            "seatLayout" : @json($event->theatre_json)
        }
        var addedSeats = []
        var removedSeats = []
        $('#seatMap').seatLayout({
            data: seatData,
            showActionButtons:false,
            numberOfSeat: 1,
            ticket_id: {{ $ticket->id }},
            callOnSeatSelect: function (_event, _data, element, selected, addedStatus) {
                if (addedStatus){
                    addedSeats.push(_data)
                    $('#addedSeats').val(JSON.stringify(addedSeats))
                }
                else {
                    removedSeats.push(_data)
                    $('#removedSeats').val(JSON.stringify(removedSeats))
                }
            }
        });
    }

    window.clickRow = (target, sectionName, row) => {
        selectedSections.some((section, index) =>{
            if (section.sectionName === sectionName){
                if(section.sectionRows.indexOf(row) === -1){
                    selectedSections[index].sectionRows.push(row)
                    if (target.className.indexOf('selected') === -1) target.className = 'selected'
                }
                else {
                    selectedSections[index].sectionRows.splice(section.sectionRows.indexOf(row), 1)
                    target.className = ''
                }
            }
        })
        $('#selectedSections').val(JSON.stringify(selectedSections))
    }
</script>
@endif
@endsection
