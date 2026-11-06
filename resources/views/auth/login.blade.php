@extends('layouts.app')

@section('meta')
    <title>{{ trans('auth.login') }} | {{ env('APP_NAME') }}</title>
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">{{ trans('auth.login') }}</div>

                    <div class="panel-body">
                        <form class="form-horizontal" method="POST" action="{{ route('login') }}">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">{{ trans('auth.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" autocomplete="email" value="{{ old('email') }}" required autofocus>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <input id="device_token" class="form-control" name="device_token" type="hidden">
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label">{{ trans('auth.password') }}</label>

                                <div class="col-md-6 flex">
                                    <input id="password" type="password" class="form-control" name="password" autocomplete="email" autocomplete="current-password" required style="padding-right: 37px">
                                    <i class="fa fa-eye" id="togglePassword" style="margin-left: -25px; margin-top:10px; cursor: pointer;"></i>
                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{ trans('auth.remember_me') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary" id="login-btn">
                                        {{ trans('auth.login') }}
                                    </button>

                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ trans('auth.forgot_your_password') }}?
                                    </a>
                                </div>
                            </div>
                            <p style="text-align: center">{!! trans('auth.login-warning') !!}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script type="module">
        if (<?php echo Session::get('deactivated_account') ? 1 : 0; ?>) {
            swal({
                text: "Your account is deactivated successfully! \n Please contact us if you want to activate your account again.",
                icon: "success"
            });
        }
        const togglePassword = document.querySelector("#togglePassword");
        const password = document.querySelector("#password");
        togglePassword.addEventListener("click", function() {
            // toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);

            // toggle the icon
            this.classList.toggle("fa-eye-slash");
        });
    </script>

    <script type="application/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            const queryString = window.location.href;
            if (queryString !== ''){
                const urlParams = new URL(queryString);
                const email = urlParams.searchParams.get('email')
                const password = urlParams.searchParams.get('password')
                const token = urlParams.searchParams.get('token')
                if (email && password){
                    window.document.getElementById('email').value = email
                    window.document.getElementById('password').value = password
                    window.document.getElementById('device_token').value = token
                    window.document.getElementById('login-btn').click()
                }
            }
        });
    </script>
@endsection
