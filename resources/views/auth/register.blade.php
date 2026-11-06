@extends('layouts.app')

@section('meta')
    <title>Register | {{ env('APP_NAME') }}</title>
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ trans('auth.register') }}</div>

                    <div class="panel-body">
                        <form class="form-horizontal" method="POST" action="{{ route('register') }}">
                            {{ csrf_field() }}
                            @php if (request('livestream') === 'true') {
                                    echo '<input type="hidden" name="livestream" value="1" />';
                            } @endphp

                            <input type="hidden" id="referral_code" name="referral_code" value="" />

                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">{{ trans('auth.name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">{{ trans('auth.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" autocomplete="email" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">{{ trans('auth.password') }}</label>

                                <div class="col-md-6 flex">
                                    <input id="password" type="password" style="padding-right: 37px" class="form-control" name="password" autocomplete="new-password" required>
                                    <i class="fa fa-eye" id="togglePassword" style="margin-left: -25px; margin-top:10px; cursor: pointer;"></i>
                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ trans('auth.password-length-warning') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password-confirm" class="col-md-4 control-label">{{ trans('auth.confirm_password') }}</label>

                                <div class="col-md-6 flex">
                                    <input id="password-confirm" style="padding-right: 37px" type="password" class="form-control" name="password_confirmation" autocomplete="new-password" required>
                                    <i class="fa fa-eye" id="confirmTogglePassword" style="margin-left: -25px; margin-top:10px; cursor: pointer;"></i>
                                </div>
                            </div>

                            <div class="col-md-offset-4">

                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <span>{{ trans('auth.already-have-account') }}<a href="/login"> {{ trans('auth.login') }}</a></span>
                                    <button type="submit" class="btn btn-primary" style="margin-left:20px">
                                        {{ trans('auth.register') }}
                                    </button>
                                </div>
                            </div>

                            <div class="text-center">
                                <p>
                                    <input type="checkbox" name="is_accepted" id="is_accepted" style="margin-right:7px;width:18px;" required>
                                    <label for="is_accepted" style="font-weight: 500;cursor: pointer;">
                                        {{ trans('auth.signup_agree') }}<a href="/terms">{{ trans('auth.terms') }}</a> {{ trans('general.and') }} <a href="/privacy">{{ trans('auth.privacy') }}</a>.
                                </p>
                                </label>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="module">
        const togglePassword = document.querySelector("#togglePassword");
        const confirmTogglePassword = document.querySelector("#confirmTogglePassword");

        const password = document.querySelector("#password");
        const confirmPassword = document.querySelector("#password-confirm");
        togglePassword.addEventListener("click", function() {
            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);
            // toggle the type attribute
            confirmPassword.setAttribute("type", type);
            // toggle the icon
            this.classList.toggle("fa-eye-slash");
            confirmTogglePassword.classList.toggle('fa-eye-slash')
        });
        confirmTogglePassword.addEventListener("click", function() {
            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);
            // toggle the type attribute
            confirmPassword.setAttribute("type", type);
            // toggle the icon
            this.classList.toggle("fa-eye-slash");
            togglePassword.classList.toggle('fa-eye-slash')
        });
    </script>
    <script>
        const params = new URLSearchParams(window.location.search);
        if (params.has('sell_tickets') && params.get('sell_tickets')) {
            if (params.has('referral') && params.get('referral')) {
                localStorage.setItem('referral_code', params.get('referral'))
            }
            document.getElementById('referral_code').value = localStorage.getItem('referral_code');
        }
    </script>
@endsection
