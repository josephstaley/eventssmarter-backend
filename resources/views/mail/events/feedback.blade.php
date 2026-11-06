@component('mail::message')
    <h2>{{ trans('event.hello') }}!</h2>
    {{ trans('event.your-tickets-are-available') }}!
    @component('mail::button', ['url' => $link_1])
        {{ trans('event.view-your-tickets') }}
    @endcomponent
    {{ trans('event.get-ready-purchasing-shop') }}!
    @component('mail::button', ['url' => $link_2])
        {{ trans('event.visit-shop') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
@endcomponent
