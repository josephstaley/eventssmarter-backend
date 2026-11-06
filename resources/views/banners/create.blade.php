@extends('layouts.app')


@section('meta')
<title>{{ trans('banner.create-banner') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    {{ trans('banner.add-banner-to') }} {{ $event->name }} @ {{ $organization->name }}
                </div>

                <div class="panel-body">

                    {{ html()->form('POST',$event->link('banners.store'))->acceptsFiles()->open() }}

                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label for="name">{{ trans('banner.banner-name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') ? old('name') : $banner->name }}" placeholder="{{ trans('banner.banner-name') }}" required>
                        @if ($errors->has('name'))
                        <span class="help-block">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                        @endif
                    </div>

                    @isset($banner->image)
                    <div class="col-sm-12">
                        <center>
                            <img src="{{ $banner->image ? $banner->image : '' }}" alt="Banner image" class="profile-img">
                        </center>
                    </div>
                    @endisset

                    <div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
                        <label>{{ trans('banner.banner-image') }} <span class="text-danger">* </span> ({!! trans('banner.banner-image-warning') !!})</label>
                        {{ html()->file('image')->class('form-control')->acceptImage()->required() }}
                        @if ($errors->has('image'))
                        <span class="help-block">
                            <strong>{{ $errors->first('image') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group{{ $errors->has('link_url') ? ' has-error' : '' }}">
                        <label>{{ trans('banner.link-url') }} <span class="text-danger">* </span> </label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-link" aria-hidden="true"></i></span>
                            <input type="text" class="form-control" name="link_url" value="{{ old('link_url') ? old('website_link') : (isset($banner->link_url) ? $banner->link_url : '') }}" placeholder="{{ trans('banner.link-url') }}" required>
                        </div>
                        @if ($errors->has('link_url'))
                        <span class="help-block">
                            <strong>{{ $errors->first('link_url') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group{{ $errors->has('main-banner-checkbox') ? ' has-error' : '' }}">
                        <label for="main-banner-checkbox">{{ trans('banner.is-main-banner') }}? &nbsp;&nbsp;</span> </label>
                        <input type="checkbox" name="main-banner-checkbox"/>
                        @if ($errors->has('main-banner-checkbox'))
                        <span class="help-block">
                            <strong>{{ $errors->first('main-banner-checkbox') }}</strong>
                        </span>
                        @endif
                    </div>


                    <hr />
                    {{ html()->button(trans('banner.create-banner'),'submit')->class('btn btn-primary') }}

                    {{ html()->form()->close() }}

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
