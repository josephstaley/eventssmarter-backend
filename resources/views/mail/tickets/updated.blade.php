@component('mail::message')
    <h2 style="text-align: center">{{ trans('event.hello') }}!</h2>
    <p style="text-align: center">{{ trans('event.you-reset-password-so-send-link-for-tickets') }}</p>
    @component('mail::button', ['url' => $link_1])
        {{ trans('event.view-your-tickets') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.get-ready-purchasing-shop') }}</p>
    @component('mail::button', ['url' => $link_2])
        {{ trans('event.visit-shop') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
    <p style="text-align: center">
        <span style="font-size: 13px;margin-top:10px">
            {{ trans('event.if-you-have-trouble-contact') }} <a href="{{ env('FRONTEND_URL') . '/contact' }}">{{ trans('checkout.here') }}</a>
        </span>
    </p>
@endcomponent
