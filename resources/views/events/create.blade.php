@extends('layouts.app')

@section('meta')
    <title>{{ trans('event.event-create') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('page-title')
    {{ trans('event.add-event') }} @ {{ $organization->name }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div>

                    {{ html()->form('POST', route('events.store', $organization))->acceptsFiles()->open() }}

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-information') }}
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                {{ html()->input('text', 'name')->class('form-control')->placeholder(trans('event.event-name'))->autofocus()->required() }}
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12">
                                {{ html()->input('file', 'image')->class('form-control') }}
                                <p class="text-danger">
                                    {{ $errors->first('image') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12">
                                {{ html()->textarea('description')->class('form-control')->placeholder(trans('event.description(optional)')) }}
                                <p class="text-danger">
                                    {{ $errors->first('description') }}
                                </p>
                                <br>
                            </div>

                            <div class="col-md-12">
                                {{ html()->input('text', 'location_name')->class('form-control')->placeholder(trans('event.location-name'))->autofocus() }}
                                <p class="text-danger">
                                    {{ $errors->first('location_name') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12 form-group{{ $errors->has('location') ? ' has-error' : '' }}">
                                <label>{{ trans('event.location') }} <span class="text-danger">* {{ trans('event.required') }}</span></label>
                                <input type="text" name="autocomplete" id="autocomplete" placeholder="{{ trans('event.loading') }}..." class="form-control" value="{{ isset($organization) ? $organization->locationString() : '' }}">
                                @if ($errors->has(['location']))
                                    <p class="text-danger">{{ $errors->first('location') }}</p>
                                @endif
                            </div>

                            <div class="col-md-12">
                                <label>{{ trans('event.facebook-pixel-id') }} <span class="text">({{ trans('event.optional') }})</span></label>
                                {{ html()->input('text', 'fb_pixel_id')->class('form-control')->placeholder(trans('event.facebook-pixel-id')) }}
                                <p class="text-danger">
                                    {{ $errors->first('fb_pixel_id') }}
                                </p>
                            </div>
                            <div class="col-md-12">
                                <label>
                                    {{ trans('event.google-analalytic-measure-id') }} <span class="text">({{ trans('event.optional') }})</span>
                                    <button type="button" class="btn-tooltip" data-toggle="tooltip" data-html="true" title="{{ trans('event.how-to-get-g-analytic-measure-id') }}?">
                                        <a target="blank" href="https://support.google.com/analytics/answer/9539598?hl=en#find-UA-ID"><i class="fa fa-question-circle"></i></a>
                                    </button>
                                </label>
                                {{ html()->input('text', 'ga_measure_id')->class('form-control')->placeholder(trans('event.google-analalytic-measure-id')) }}
                                <p class="text-danger">
                                    {{ $errors->first('ga_measure_id') }}
                                </p>
                            </div>
                            @if ($organization->category == 'Political')
                                <div class="col-md-12">
                                    {{ html()->textarea('statement', '')->class('form-control')->placeholder(trans('event.statement')) }}
                                    <p class="text-danger">
                                        {{ $errors->first('statement') }}
                                    </p>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <label>{{ trans('event.input-prompt-des') }} <span class="text">({{ trans('event.optional') }})</span></label>
                                {{ html()->textarea('prompt', '')->class('form-control')->placeholder(trans('event.prompt')) }}
                                <p class="text-danger">
                                    {{ $errors->first('prompt') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-privacy') }}
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <label for="is_private" style="min-width:55px">{{ trans('event.private') }}&nbsp;</label>
                                <input type="hidden" name="is_private" value="0" />
                                <input type="checkbox" name="is_private" style="height:12px;">
                            </div>
                        </div>
                    </div>

                    @component('partials.time_form_create', ['field_name' => 'starts_at'])
                        @slot('title')
                            {{ trans('event.event-start') }}
                        @endslot
                        {{ trans('event.what-date-and-time-event-start') }}?
                    @endcomponent

                    @component('partials.time_form_create', ['field_name' => 'ends_at'])
                        @slot('title')
                            {{ trans('event.event-end') }}
                        @endslot
                        {{ trans('event.what-date-and-time-event-end') }}?
                    @endcomponent

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-pdf') }}
                        </div>
                        <div class="panel-body">
                            <div class="col-md-12">
                                <input type="file" name="pdf[]" id="pdf" class='form-control' multiple>
                                @if ($errors->has(['pdf']))
                                    <p class="text-danger">{{ $errors->first('pdf') }}</p>
                                @endif
                                <br>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-preview-video') }}
                        </div>
                        <div class="panel-body">
                            <input type="file" name="video" id="video" class="form-control">
                            <p class="text-danger" id="video-upload-text"></p>
                            <p class="text-danger">
                                {{ $errors->first('video') }}
                            </p>
                        </div>
                    </div>

                    @component('partials.theatres', ['theatres' => $theatres])
                        @slot('title')
                            {{ trans('event.select-venue-for-seating') }}
                        @endslot
                    @endcomponent

                    {{ html()->button(trans('event.create-event'), 'submit')->class('btn btn-primary pull-right')->id('submit-btn') }}

                    {{ html()->form()->close() }}

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="module">
        $('form').submit(() => {
            $("#submit-btn").attr("disabled", true);
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_FRONT_KEY') }}&libraries=places&callback=initAutocomplete" async defer></script>
    <script>
        function initAutocomplete() {
            var componentForm = {
                street_number: 'short_name',
                route: 'long_name',
                locality: 'long_name',
                administrative_area_level_1: 'short_name',
                country: 'long_name',
                postal_code: 'short_name'
            };

            var input = document.getElementById('autocomplete');
            input.setAttribute('placeholder', '{{ trans('event.enter-street-address') }} (e.g. 2401 Tampa Dr, Orlando Fl 34219)');

            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode']
            });
        }
    </script>
    <script type="module">
        $('.btn-tooltip').tooltip();

        const loadVideo = file => new Promise((resolve, reject) => {
            try {
                let video = document.createElement('video')
                video.preload = 'metadata'

                video.onloadedmetadata = function() {
                    resolve(this)
                }

                video.onerror = function() {
                    reject("{{ trans('event.invalid-video') }}.")
                }

                video.src = window.URL.createObjectURL(file)
            } catch (e) {
                reject(e)
            }
        })

        $("#video").on("change", async function(e) {
            try {
                const video = await loadVideo(e.currentTarget.files[0])
                if (video.duration === Infinity) {
                    $('#video-upload-text').html('{{ trans('event.invalid-video-format') }}!')
                    return;
                } else {
                    $('#video-upload-text').html('')
                }
                if (video.duration > 90) {
                    $('#video-upload-text').html(
                        '{{ trans("event.invalid-video-length") }}.')
                    return;
                } else {
                    $('#video-upload-text').html('')
                }
            } catch (error) {
                $('#video-upload-text').html(error)
            }
        });
    </script>
@endsection
