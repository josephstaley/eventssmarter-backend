@component('mail::message')
    <h2 style="text-align: center">{{ trans('event.hello') }}!</h2>
    <p style="text-align: center">Below is the meeting summary.</p>
    <p style="text-align: center; font-size:14px;color:black;">{{ $text }}</p>
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
@endcomponent
