@extends('layouts.app')

@section('meta')
    <title>{{ trans('organization.edit-organization') }} | {{ env('APP_NAME') }}</title>
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
@endsection

@section('content')
    <div class="container">
        <div class="row">

            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.edit') }} {{ $organization->name }}
                    </div>

                    <div class="panel-body">

                        {{ html()->form('PATCH', route('organization.update', $organization))->acceptsFiles()->open() }}

                        @include('organization._form', ['orgCreate' => 0])

                        <br>

                        {{-- {{ html()->button('Update Organization','submit')->class('btn btn-primary') }} --}}

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>

            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a id="dangerous">
                            <h4 class="text-danger">{{ trans('organization.dangerous') }}</h4>
                        </a>
                    </div>

                    <div class="panel-body">

                        {{ html()->form('POST', route('organization.change', $organization))->open(['id' => 'organization-form']) }}

                        <button onclick="return confirm('{{ trans('organization.are-you-sure-change-password') }}.')" class="btn btn-danger" type="submit">{{ trans('organization.assign-new-scan-code') }}</button>

                        {{ html()->form()->close() }}

                        <br>

                        {{ html()->form('DELETE', route('organization.destroy', $organization))->open() }}

                        <button onclick="return confirm('{{ trans('organization.are-you-sure-delete-organization') }}?')" class="btn btn-danger" type="submit">{{ trans('organization.delete-organization') }}</button>

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('scripts1')
    <script type="module">
    $(document).ready(() =>{
        if (window.location.href.includes('dangerous')){
            $('html, body').animate({
                scrollTop: $("#dangerous").offset().top - 125
            });
        }
    });
</script>
@endsection
