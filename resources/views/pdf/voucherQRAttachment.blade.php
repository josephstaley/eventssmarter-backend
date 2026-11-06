<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>

<div style="width: 800px;border: 1px dotted black; padding: 20px;margin:0 auto;margin-bottom:20px;">
    <div style="padding: 20px;">
        <h1>
            {{ env('APP_NAME') }} {{ trans('event.ticket') }} | {{ $voucher->event->organization->name }}
        </h1>
        <h3>{{ $voucher->detail_text }} @ <a href="{{ env('FRONTEND_URL') . '/' . $voucher->event->organization->slug . '/' . $voucher->event->slug . '_' . $voucher->event->id }}">{{ $voucher->event->name }}</a> </h3>
        <p class="description">
            {{ optional($voucher->ticket)->description }}
        </p>
        @if ($voucher->ticket_type === 'seat')
            <a href="{{ env('FRONTEND_URL') }}/preview-theatre/event/{{ $voucher->event->id }}/voucher/{{ $voucher->id }}">{{ trans('checkout.view-seat-on-map') }}</a>
        @endif
        @if (isset($voucher->ticket) && $voucher->ticket->is_live_stream)
            {{ trans('general.live-stream') }}: <a href="{{ $voucher->ticket->stream_type === 'eventssmarter' ? 'https://live.eventssmarter.com/' . $voucher->ticket->attendee_url : env('FRONTEND_URL') . '/stream/' . $voucher->ticket->id }}">{{ trans('general.view-live-stream') }}</a>
        @endif
        <p>{{ trans('general.purchased-by') }}: {{ $user->name }} ({{ $user->email }})</p>
        <span>{{ trans('event.tickets') }} #{{ env('APP_NAME') }}_{{ $voucher->id }}</span>
        <p>
            <span>{{ $voucher->event->starts_at->format('l M j g:ia') }}</span>
            <span style="padding-left:10px;padding-right: 10px;">{{ trans('event.to') }}</span>
            <span>{{ $voucher->event->ends_at->format('l M j g:ia') }}</span>
        </p>
    </div>
    <div style="">
        <img src="data:image/png;base64,{!! base64_encode($voucher->qr_code) !!}" alt="Ticket QR Code">
    </div>
    <div style="clear:both"></div>
</div>
<script type="module">
    function urlify(text) {
        var urlRegex = /(https?:\/\/[^\s]+)/g;
        return text.replace(urlRegex, function(url) {
            return '<a target="blank" href="' + url + '">' + url + '</a>';
        })
    }
    $('.description').each(function() {
        var content = urlify($(this).html());
        $(this).html(content);
    });
</script>
