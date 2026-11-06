@component('mail::message')
    <h2 style="text-align: center">{{ trans('event.hello') }}!</h2>
    <p style="text-align: center">{{ $content }}</p>
    @component('mail::button', ['url' => env('FRONTEND_URL') . '/login'])
        {{ trans('event.login-to-your-account') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
    <div style="text-align: center;margin-top:50px;">
        <a style="text-align: center;display:block;" href="{{ env('APP_URL') }}/unsubscribe/{{ $unsubscribe_token }}">
            {{ trans('event.unsubscribe') }}
        </a>
        <br>
    </div>
@endcomponent
