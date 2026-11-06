@component('mail::message')
    <h2 style="text-align: center">{{ trans('event.hello') }}, {{ trans('event.camera-crew-team') }}!</h2>
    <p style="text-align: center">{{ trans('event.you-are-invited-on') }} {{ $eventname }} {{ trans('event.by') }} {{ $name }}</p>
    <p style="text-align: center">{{ trans('event.you-can-download-config-file-for-livestream') }}!</p>
    @component('mail::button', ['url' => $link])
        {{ trans('event.view-event') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.here-are-the-steps-for-livestream') }}.</p>
    @component('mail::button', ['url' => 'https://www.eventssmarter.com/live-stream-production-instructions'])
        {{ trans('event.visit-guide') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
@endcomponent
