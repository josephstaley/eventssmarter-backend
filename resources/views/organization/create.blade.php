@extends('layouts.app')

@section('meta')
    <title>{{ trans('organization.create-organization') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('page-title')
    {{ trans('organization.create-organization') }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('organization.organization-information') }}
                    </div>

                    <div class="panel-body">

                        {{ html()->form('POST',route('organization.store'))->acceptsFiles()->open() }}

                        @include('organization._form', ['orgCreate' => 1])

                        <br>

                        {{-- {{ html()->button('Create Organization','submit')->class('btn btn-primary') }} --}}

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
