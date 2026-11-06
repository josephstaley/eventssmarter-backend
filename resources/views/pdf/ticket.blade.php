<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="https://code.jquery.com/jquery-1.12.4.min.js" defer></script>
@foreach ($vouchers as $voucher)
    <div style="width: 800px;border: 1px dotted black; padding: 20px;margin:0 auto;margin-bottom:20px;">
        <div style="padding: 20px;">
            <h1>
                {{ env('APP_NAME') }} {{ trans('event.ticket') }} | {{ $ticket->event->organization->name }}
            </h1>
            <h3>{{ $ticket->name }} @ <a href="{{ env('FRONTEND_URL').'/'. $ticket->event->organization->slug .'/'.$ticket->event->slug.'_'.$ticket->event->id}}">{{ $ticket->event->name }}</a> </h3>
            <p class="description">{{ $ticket->description }}</p>
            @if ($ticket->is_live_stream)
                {{ trans('general.live-stream') }}: <a href="{{ route('tickets.stream', [$ticket->event->organization, $ticket->id]) }}">{{ trans('general.view-live-stream') }}</a>
            @endif
            <p>{{ trans('general.purchased-by') }}: {{ $user->name }} ({{ $user->email }})</p>
            <span>{{ trans('event.tickets') }} #{{ env('APP_NAME') }}_{{ $voucher->id }}</span>
            <p>
                <span>{{ $ticket->event->starts_at->format('l M j g:ia') }}</span>
                <span style="padding-left:10px;padding-right: 10px;">{{ trans('event.to') }}</span>
                <span>{{ $ticket->event->ends_at->format('l M j g:ia') }}</span>
            </p>
        </div>
        <div style="">
            <img src="data:image/png;base64,{!! base64_encode($voucher->qr_code) !!}" alt="Ticket QR Code">
        </div>
        <div style="clear:both"></div>
    </div>
    @if ($loop->iteration % 2 == 0 && $loop->remaining > 0)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
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
