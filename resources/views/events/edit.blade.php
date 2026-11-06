@extends('layouts.app')

@section('meta')
    <title>{{ trans('event.edit-event') }} | {{ env('APP_NAME') }}</title>
@endsection

@section('style')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
@endsection

@section('page-title')
    {{ trans('event.edit-event') }} @ {{ $organization->name }}
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div>
                    {{ html()->form('POST', route('events.update', [$organization, $event->id]))->acceptsFiles()->open() }}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-information') }}
                        </div>
                        <div class="panel-body">
                            <div class="col-sm-12">
                                <center>
                                    <img class="event-img" src="{{ $event->image }}" alt="{{ $event->name }}">
                                </center>
                                <br />
                            </div>
                            <div class="col-md-12">
                                {{ html()->input('file', 'image')->class('form-control') }}
                                <p class="text-danger">
                                    {{ $errors->first('image') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12">
                                {{ html()->input('text', 'name')->class('form-control')->placeholder(trans('event.event-name'))->value($event->name)->autofocus()->required() }}
                                <p class="text-danger">
                                    {{ $errors->first('name') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12">
                                {{ html()->textarea('description')->class('form-control')->value($event->description)->placeholder(trans('event.description(optional)')) }}
                                <p class="text-danger">
                                    {{ $errors->first('description') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12">
                                {{ html()->input('text', 'location_name')->class('form-control')->placeholder(trans('event.location-name'))->value($event->location_name)->autofocus() }}
                                <p class="text-danger">
                                    {{ $errors->first('location_name') }}
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12 {{ $errors->has('location') ? ' has-error' : '' }}">
                                <label>{{ trans('event.location') }} <span class="text-danger">* {{ trans('event.required') }}</span></label>
                                <input type="text" name="autocomplete" id="autocomplete" placeholder="{{ trans('event.loading') }}..." class="form-control" value="{{ isset($event) ? $event->locationString() : '' }}">
                                @if ($errors->has(['location']))
                                    <p class="text-danger">{{ $errors->first('location') }}</p>
                                @endif
                                <br>
                            </div>
                            <div class="col-md-12">
                                <label>{{ trans('event.facebook-pixel-id') }} <span class="text">({{ trans('event.optional') }})</span></label>
                                {{ html()->input('text', 'fb_pixel_id')->class('form-control')->placeholder(trans('event.facebook-pixel-id'))->value($event->fb_pixel_id)->autofocus() }}
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
                                {{ html()->input('text', 'ga_measure_id')->class('form-control')->placeholder(trans('event.google-analalytic-measure-id'))->value($event->ga_measure_id) }}
                                <p class="text-danger">
                                    {{ $errors->first('ga_measure_id') }}
                                </p>
                            </div>
                            {{-- <div class="col-md-12" style="margin-top:15px">
                                {{ html()->input('text','ad_link')->class('form-control')->placeholder('AD link')->value($event->ad_link)->autofocus() }}
                                <p class="text-danger">
                                    {{ $errors->first('ad_link') }}
                                </p>
                            </div> --}}
                            @if ($organization->category == 'Political')
                                <div class="col-md-12">
                                    {{ html()->textarea('statement', $event->statement)->class('form-control')->placeholder(trans('event.statement')) }}
                                    <p class="text-danger">
                                        {{ $errors->first('statement') }}
                                    </p>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <label>{{ trans('event.input-prompt-des') }} <span class="text">({{ trans('event.optional') }})</span></label>
                                {{ html()->textarea('prompt', $event->prompt)->class('form-control')->placeholder(trans('event.prompt')) }}
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
                                <input type="checkbox" name="is_private" style="height:12px;" <?php echo $event->is_private ? 'checked' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    @component('partials.time_form', [
                        'field_name' => 'starts_at',
                        'time' => \Carbon\Carbon::parse($event->starts_at),
                    ])
                        @slot('title')
                            {{ trans('event.event-start') }}
                        @endslot
                        {{ trans('event.what-date-and-time-event-start') }}?
                    @endcomponent

                    @component('partials.time_form', ['field_name' => 'ends_at', 'time' => \Carbon\Carbon::parse($event->ends_at)])
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
                                <p id='pdf_error' class="text-danger">
                                </p>
                                <p id='pdf_sucess' class="panel-heading">
                                </p>
                                <br>
                            </div>
                            <div class="col-md-12">
                                <table id="pdf_list" class="display">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('auth.name') }}</th>
                                            <th>{{ trans('general.menu.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.event-preview-video') }}
                        </div>
                        <div class="panel-body">
                            @if ($event->video)
                                <div>
                                    <a class="del-video" onclick="videoDelete({{ $event->id }})">
                                        <video src="{{ $event->video }}"></video>
                                        <span><i class="fa fa-trash"></i></span>
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="video" id="video" class="form-control">
                            <p class="text-danger" id="video-upload-text"></p>
                            <p class="text-danger">
                                {{ $errors->first('video') }}
                            </p>
                        </div>
                    </div>

                    @component('partials.theatres', ['theatres' => $theatres, 'theatre_id' => $event->theatre_id])
                        @slot('title')
                            {{ trans('event.select-venue-for-seating') }}
                        @endslot
                    @endcomponent

                    {{ html()->button(trans('event.update-event'), 'submit')->class('btn btn-primary pull-right')->id('submit-btn') }}

                    {{ html()->form()->close() }}

                    <br /><br />

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('event.delete-event') }}
                        </div>
                        <div class="panel-body">
                            <a href="{{ route('events.destroy', $event) }}" class="btn btn-danger" onclick="return confirm('{{ trans('event.delete-confirm-des') }}')">{{ trans('banner.delete') }}</a>
                        </div>
                    </div>

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
            input.setAttribute('placeholder', '{{ trans("event.enter-street-address") }} (e.g. 2401 Tampa Dr, Orlando Fl 34219)');

            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode']
            });

        }
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js" defer></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>

    <script type="module">
        $(function() {
            var table = $('#pdf_list').DataTable({
                processing: true,
                serverSide: true,
                "bPaginate": false,
                searching: false,
                paging: false,
                info: false,
                "bSort": false,
                ajax: "{{ url('/event/fetch-pdf-files/' . $event->id) }}",
                columns: [{
                        data: 'name',
                        name: 'name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

        });

        window.loadVideo = file => new Promise((resolve, reject) => {
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
                    $('#video-upload-text').html('{{ trans("event.invalid-video-format") }}!')
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

        $("#pdf").on("change", function(e) {
            swal({
                title: "Please wait",
                text: "{{ trans('event.file-upload-server') }}!",
                buttons: false,
                closeOnClickOutside: false,
                icon: "warning"
            });
            var formData = new FormData();
            // Read selected files
            var totalfiles = document.getElementById('pdf').files.length;
            for (var index = 0; index < totalfiles; index++) {
                formData.append("pdf[]", document.getElementById('pdf').files[index]);
            }

            formData.append('_token', "{{ csrf_token() }}");


            //var filename = $("#pdf").val();

            //var extension = filename.replace(/^.*\./, '');

            //if (extension =='pdf') {
            $.ajax({
                url: "{{ url('/event/pdf-upload/' . $event->id) }}",
                type: 'POST',
                data: formData,
                processData: false, // tell jQuery not to process the data
                contentType: false, // tell jQuery not to set contentType
                success: function(data) {

                    let obj = JSON.parse(data);

                    if (obj.sucess) {
                        $('#pdf_error').html('');
                        $('#pdf_sucess').html('{{ trans("event.file-uploaded") }}');

                        $('#pdf_list').DataTable().ajax.reload();
                        swal("{{ trans('event.upload-finished') }}!", "{{ trans('event.thanks-for-your-patience') }}!", "success");
                        $('#pdf').val('');
                    } else {
                        $('#pdf_error').html('');
                        $('#pdf_sucess').html(obj.message);
                        $('#pdf').val('');
                    }
                }
            });
            //} else {
            //  $('#pdf_error').html("Please upload only pdf file");
            //  $('#pdf_sucess').html('');
            //}
        });

        $("#pdf_list").on("click", ".delete-record", function() {

            let id = $(this).attr('data-url');

            swal({
                    title: "{{ trans('event.are-you-sure') }}?",
                    text: "{{ trans('event.once-deleted-not-recover-pdf-file') }}!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal({
                            title: "{{ trans('event.please-wait') }}",
                            text: "{{ trans('event.file-is-being-deleted-now') }}!",
                            buttons: false,
                            closeOnClickOutside: false,
                            icon: "warning"
                        });
                        $.ajax({
                            url: "{{ url('event/pdf_remove') }}",
                            type: 'GET',
                            data: {
                                id: id,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(msg) {

                                swal("{{ trans('event.deleted') }}!", "{{ trans('event.your-record-deleted') }}", "success");
                                $('#pdf_list').DataTable().ajax.reload();
                            }
                        });
                    }
                });
        });

        window.videoDelete = (event_id) => {
            swal({
                    title: "{{ trans('event.are-you-sure') }}?",
                    text: "{{ trans('event.once-deleted-you-will-not-recover-video') }}!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        swal({
                            title: "{{ trans('event.please-wait') }}",
                            text: "{{ trans('event.video-file-deleted-now') }}!",
                            buttons: false,
                            closeOnClickOutside: false,
                            icon: "warning"
                        });
                        $.ajax({
                            url: "{{ url('event/video_remove') }}",
                            type: 'GET',
                            data: {
                                event_id: event_id,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(msg) {
                                swal("Deleted!", "{{ trans('event.preview-video-flie-deleted') }}", "success");
                                $('.del-video')[0].remove();
                            }
                        });
                    }
                });
        }
    </script>
@endsection
