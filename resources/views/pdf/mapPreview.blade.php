@extends('layouts.app')

@section('meta')
<title>Preview Ticket on Map</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<link rel="stylesheet" type="text/css" href="{{ env('APP_URL') }}/css/seatLayout.css">
@if ($ticket->event->theatre_id && $ticket->event->theatre_json)
<script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
<script src="{{ env('APP_URL') }}/js/svg-inject.min.js" defer></script>
@endif
@endsection

@section('content')
<div style="text-align: center;margin-bottom:20px;">
    <h3 style="margin-bottom: 10px">{{ $ticket->event->theatre->name }}</h3>
    @foreach($voucher->seat as $item)
    <h5>{{ $item->AreaDesc.', Row '.$item->PhyRowId.', Seat No. '.(isset($item->seatText) ? $item->seatText : $item->seatNumber) }}</h5>
    @endforeach
</div>
@if ($ticket->event->theatre->svg_path)
<div class="row" id="seatBox">
    <div class="col-md-6">
        <img src="{{ $ticket->event->theatre->svg_path }}" onload="SVGInject(this)" class="stadium-svg"/>
    </div>
    <div class="col-md-6">
        <div id="seatMap">
        </div>
    </div>
</div>
@else
<div id="seatMap" style="overflow: auto"></div>
@endif
@endsection

@section('scripts')
<script src="{{ env('APP_URL') }}/js/seatMapLayout.js" defer></script>
<script type="module">
    if ('{{ $ticket->event->theatre->svg_path }}'){
        SVGInject.setOptions({
            makeIdsUnique: false,
            useCache: false,
            beforeInject: function(img, svg) {
                svg.classList.add('stadium-svg');
            },
        });
        const seatJson = JSON.parse('{!! $voucher->seat_json !!}')
        var seatLayout = @json($ticket->event->theatre_json);
        seatLayout.colAreas.objArea = [seatLayout.colAreas.objArea[seatJson[0]['AreaNum'] - 1]]
        seatLayout.colAreas.intMaxSeatId = seatLayout.colAreas.objArea[0].intMaxSeatId - 1;
        $(document).ready(() =>{
            setTimeout(() => {
                $('#' + seatJson[0]['AreaDesc']).addClass('mySeats')
            }, 1000);
        })
        var seatData = {
            "theater_id" : '{{ $ticket->event->theatre_id }}',
            seatLayout
        }
        $('#seatMap').seatLayout({
            data: seatData,
            showActionButtons:false,
            numberOfSeat: 0,
            voucher: JSON.parse('{!! $voucher->seat_json !!}'),
            stageShow: false
        });
    }
    else {
        var seatData = {
            "theater_id" : '{{ $ticket->event->theatre_id }}',
            "seatLayout" : @json($ticket->event->theatre_json)
        }
        $('#seatMap').seatLayout({
            data: seatData,
            showActionButtons:false,
            numberOfSeat: 0,
            voucher: JSON.parse('{!! $voucher->seat_json !!}')
        });
    }
</script>
@endsection
