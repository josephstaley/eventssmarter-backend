@component('mail::message')
    <h2 style="text-align: center">{{ trans('event.hello') }}!</h2>
    <p style="text-align: center">{{ trans('event.your-temporary-account-login-is') }}: {{ $password }}</p>
    <p style="text-align: center">{{ trans('event.login-to-your-account-to-access-tickets') }}.</p>
    <p style="text-align: center">{{ trans('event.you-can-change-the-password-in-the-edit-page') }}.</p>
    @component('mail::button', ['url' => env('FRONTEND_URL') . '/login'])
        {{ trans('event.login-to-your-account') }}
    @endcomponent
    <p style="text-align: center">{{ trans('event.thank-you-using') }} {{ env('APP_NAME') }}!</p>
@endcomponent
