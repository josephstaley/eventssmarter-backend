@extends('layouts.app')

@section('meta')
    <title>{{ trans('event.specify-location') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.edit') }} "{{ $event->name }}" {{ trans('event.location') }}
                    </div>

                    <div class="panel-body">

                        {{ html()->form('PATCH', route('location.update', $event))->open() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label>{{ trans('checkout.address') }}</label>
                            <input type="text" name="autocomplete" id="autocomplete" class="form-control" value="{{ $event->address }}">
                            @if ($errors->has('string'))
                                <p class="text-danger">{{ $errors->first('string') }}</p>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('location_name') ? ' has-error' : '' }}">
                            <label>{{ trans('event.name-location(optional)') }}</label>
                            <input type="text" name="location_name" class="form-control" value="{{ old('location_name') ?? $event->location_name }}" placeholder="{{ trans('event.ex-baseball-stadium') }}">
                            @if ($errors->has('location_name'))
                                <p class="text-danger">{{ $errors->first('location_name') }}</p>
                            @endif
                        </div>

                        <br>

                        <button type="submit" class="btn btn-primary">{{ trans('event.save-location') }}</button>

                        {{ html()->form()->close() }}

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
            input.setAttribute('placeholder', '{{ trans("event.enter-street-address") }} (e.g. 2401 Tampa Dr, Orlando Fl 34219)');

            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode']
            });
        }
    </script>
@endsection
