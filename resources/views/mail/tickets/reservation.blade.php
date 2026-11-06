@component('mail::message')
    <h2 style="text-align: center">{{ trans('event.hello') }}!</h2>
    <p style="text-align: center">{{ trans('event.your-reservation-scheduled-at') }}:</p>
    <p style="text-align: center">{{ $reservation_date }}</p>
    <p style="text-align: center">{{ $reservation_time }}</p>
    <p style="text-align: center;margin-top:20px;">{{ trans('event.get-ready-purchasing-shop') }}</p>
    @component('mail::button', ['url' => $ad_link])
        {{ trans('event.visit-shop') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
    <p style="text-align: center">
        <span style="font-size: 13px;margin-top:10px">
            {{ trans('event.if-you-have-trouble-contact') }} <a href="{{ env('FRONTEND_URL') . '/contact' }}">{{ trans('checkout.here') }}</a>
        </span>
    </p>
@endcomponent
