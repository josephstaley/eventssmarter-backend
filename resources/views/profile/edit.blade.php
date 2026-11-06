@extends('layouts.app')

@section('content')
    <div class="container">
        <a href="{{ route('home') }}"><i class="fa fa-hand-o-left"></i> {{ trans('event.back-to-dashboard') }}</a>
        <br><br>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('general.home.edit-profile') }}
                    </div>

                    <div class="panel-body">
                        <div>
                            <br />
                            <form action="{{ route('update.profile', $user->id) }}" id="profile-form" method="post" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <div class="col-sm-12">

                                    <center>
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }} avatar" class="avatar">
                                    </center>
                                    <br /><br />

                                    <div class="form-group{{ $errors->has('avatar') ? ' has-error' : '' }}">
                                        <input type="file" class="form-control" name="avatar" style="padding: 5px;">
                                        @if ($errors->has('avatar'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('avatar') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                                        <input type="text" class="form-control" name="name" value="{{ old('name') ? old('name') : $user->name }}" placeholder="{{ trans('auth.name') }}">
                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                                        <input type="text" class="form-control" name="title" value="{{ old('title') ? old('title') : $user->title }}" placeholder="{{ trans('general.home.your-title') }}">
                                        @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('title') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('phone_number') ? ' has-error' : '' }}">
                                        <input type="text" class="form-control" name="phone_number" value="{{ old('phone_number') ? old('phone_number') : $user->phone_number }}" placeholder="{{ trans('checkout.phone-number') }}">
                                        @if ($errors->has('phone_number'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('phone_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <hr>

                                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label>{{ trans('general.home.change-email') }}</label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email') ? old('email') : $user->email }}" placeholder="{{ trans('auth.email') }}">
                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <hr>

                                    <div class="form-group{{ $errors->has('website_link') ? ' has-error' : '' }}">
                                        <label>{{ trans('organization.website-fulllink') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-link" aria-hidden="true"></i></span>
                                            <input type="text" class="form-control" name="website_link" value="{{ old('website_link') ? old('website_link') : $user->website_link }}" placeholder="{{ trans('organization.website-fulllink') }}">
                                        </div>
                                        @if ($errors->has('website_link'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('website_link') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('facebook_link') ? ' has-error' : '' }}">
                                        <label>{{ trans('organization.facebook-fulllink') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-facebook-official" aria-hidden="true"></i></span>
                                            <input type="text" class="form-control" name="facebook_link" value="{{ old('facebook_link') ? old('facebook_link') : $user->facebook_link }}" placeholder="{{ trans('organization.facebook-fulllink') }}">
                                        </div>
                                        @if ($errors->has('facebook_link'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('facebook_link') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('instagram_link') ? ' has-error' : '' }}">
                                        <label>{{ trans('organization.instagram-fulllink') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-instagram" aria-hidden="true"></i></span>
                                            <input type="text" class="form-control" name="instagram_link" value="{{ old('instagram_link') ? old('instagram_link') : $user->instagram_link }}" placeholder="{{ trans('organization.instagram-fulllink') }}">
                                        </div>
                                        @if ($errors->has('instagram_link'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('instagram_link') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-group{{ $errors->has('linkedin_link') ? ' has-error' : '' }}">
                                        <label>{{ trans('organization.linkedin-fulllink') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-linkedin-square" aria-hidden="true"></i></span>
                                            <input type="text" class="form-control" name="linkedin_link" value="{{ old('linkedin_link') ? old('linkedin_link') : $user->linkedin_link }}" placeholder="{{ trans('organization.linkedin-fulllink') }}">
                                        </div>
                                        @if ($errors->has('linkedin_link'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('linkedin_link') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="form-group{{ $errors->has('twitter_link') ? ' has-error' : '' }}">
                                        <label>{{ trans('organization.twitter-fulllink') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-twitter-square" aria-hidden="true"></i></span>
                                            <input type="text" class="form-control" name="twitter_link" value="{{ old('twitter_link') ? old('twitter_link') : $user->twitter_link }}" placeholder="{{ trans('organization.twitter-fulllink') }}">
                                        </div>
                                        @if ($errors->has('twitter_link'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('twitter_link') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <br /><br />
                                    <button type="submit" class="btn btn-primary pull-right">{{ trans('general.home.save-changes') }}</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('general.home.change-password') }}
                    </div>

                    <div class="panel-body">
                        <div>
                            <br />
                            <form action="{{ route('change.password', $user->id) }}" method="post">
                                {{ csrf_field() }}

                                <div class="col-sm-12">
                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label>{{ trans('general.home.new-password') }}</label>
                                        <input id="password" type="password" class="form-control" name="password" placeholder="{{ trans('general.home.new-password') }}" required>
                                        @if ($errors->has('password'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('password') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    <label>{{ trans('general.home.retype-new-password') }}</label>
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="{{ trans('general.home.retype-new-password') }}" required>
                                </div>

                                <div class="col-sm-12">
                                    <br /><br />
                                    <button type="submit" class="btn btn-danger pull-right">{{ trans('general.home.change-password') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('general.home.deactivate-account') }}
                    </div>

                    <div class="panel-body">
                        <div>
                            <form action="{{ route('deactivate.profile', $user->id) }}" method="post" onsubmit="return confirm('{{ trans('general.home.are-you-sure-deactivate-account') }}?')">
                                {{ csrf_field() }}
                                <button type="submit" class="btn btn-danger">{{ trans('general.home.deactivate-account') }}</button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="module">
    $("#profile-form").submit(() => {
        let website_link = $('[name="website_link"]').val() ? $('[name="website_link"]').val().toLowerCase() : null;
        let facebook_link = $('[name="facebook_link"]').val() ? $('[name="facebook_link"]').val().toLowerCase() : null;
        let instagram_link = $('[name="instagram_link"]').val() ? $('[name="instagram_link"]').val().toLowerCase() : null;
        let linkedin_link = $('[name="linkedin_link"]').val() ? $('[name="linkedin_link"]').val().toLowerCase() : null;
        let twitter_link = $('[name="twitter_link"]').val() ? $('[name="twitter_link"]').val().toLowerCase() : null;

        if(!website_link.startsWith("https://") && !website_link.startsWith("http://")) {
            website_link = "https://" + website_link;
            $('[name="website_link"]').val(website_link);
        }

        if(!facebook_link.startsWith("https://") && !facebook_link.startsWith("http://")) {
            facebook_link = "https://" + facebook_link;
            $('[name="facebook_link"]').val(facebook_link);
        }

        if(!instagram_link.startsWith("https://") && !instagram_link.startsWith("http://")) {
            instagram_link = "https://" + instagram_link;
            $('[name="instagram_link"]').val(instagram_link);
        }

        if(!linkedin_link.startsWith("https://") && !linkedin_link.startsWith("http://")) {
            linkedin_link = "https://" + linkedin_link;
            $('[name="linkedin_link"]').val(linkedin_link);
        }

        if(!twitter_link.startsWith("https://") && !twitter_link.startsWith("http://")) {
            twitter_link = "https://" + twitter_link;
            $('[name="twitter_link"]').val(twitter_link);
        }

        return true;
    })
</script>
@endsection
