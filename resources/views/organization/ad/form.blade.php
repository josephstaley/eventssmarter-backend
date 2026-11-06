@extends('layouts.app')

@section('page-title')
    {{ trans('organization.create-organization-ad') }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('organization.organization-ad-information') }}
                    </div>

                    <div class="panel-body">

                        {{ html()->form('POST', route('organization.ad.store', $organization->id))->acceptsFiles()->open() }}

                        @isset($organization->ad_image)
                            <div class="col-sm-12">
                                <br /><br />
                                <center>
                                    <img src="{{ $organization->ad_image ? $organization->ad_image : '' }}" alt="Organization Ad Photo" class="profile-img">
                                </center>
                            </div>
                        @endisset
                        <label>{{ trans('event.profile-image') }}</label>
                        {{ html()->file('ad_image')->class('form-control')->acceptImage() }}
                        @if ($errors->has('ad_image'))
                            <p class="text-danger">{{ $errors->first('ad_image') }}</p>
                        @endif

                        <hr>

                        <div class="form-group{{ $errors->has('ad_link') ? ' has-error' : '' }}">
                            <label>{{ trans('event.ad-link') }}</label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-link" aria-hidden="true"></i></span>
                                <input type="text" class="form-control" name="ad_link" value="{{ old('ad_link') ? old('ad_link') : (isset($organization->ad_link) ? $organization->ad_link : '') }}" placeholder="{{ trans('event.full-ad-link') }}">
                            </div>
                            @if ($errors->has('ad_link'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('ad_link') }}</strong>
                                </span>
                            @endif
                        </div>

                        <br>

                        {{ html()->button(trans('event.submit'), 'submit')->class('btn btn-primary') }}

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
