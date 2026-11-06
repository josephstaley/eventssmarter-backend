<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-6 uppercase">
                {{ trans('general.home.my-live-streams') }}
            </div>
            <div class="col-md-6 col-sm-6 col-xs-6">
                <a class="pull-right" onclick="showNewLiveStreamModal()" style="cursor: pointer">
                    <i class="fa fa-video-camera"></i> {{ trans('general.home.new-stream') }}
                </a>
            </div>
        </div>
    </div>

    <div class="panel-body">
        @foreach($streams as $stream)
            <div class="panel-link row">
                <div class="col-xs-10">
                    <h4>
                        @if ($stream->is_private)
                        <i class="fa fa-lock"></i>
                        @endif
                        <a href="https://call.eventssmarter.com/streams/{{ $stream->id }}?{{ $login_token }}" class="text">
                            {{ $stream->name }}
                        </a>
                    </h4>
                </div>
                <div class="col-xs-1" style="padding:0px">
                    <a class="btn text tooltip-text" onclick="getInviteLink({{ $stream->id }}, '{{ $encrypted_login_token }}', this)">
                        <i class="fa fa-share-alt"></i>
                        <span class="tooltiptext">{{ trans('event.get-invitation-link') }}</span>
                    </a>
                </div>
                <div class="col-xs-1" style="padding:0px">
                    <a class="btn text" onclick="deleteLiveStream({{ $stream->id }})">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="modal fade" id="newLiveStream" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-video-camera"></i> {{ trans('general.home.new-live-stream') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group flex">
                        <label for="new_name" class="col-md-2 col-sm-2 col-xs-2" style="margin-top: 7px;text-align:right;">{{ trans('auth.name') }}</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input class="form-control" id="new_name">
                        </div>
                    </div>
                    <div>
                        <label for="is_private" style="min-width:75px;text-align:right;" class="col-md-2 col-sm-2 col-xs-2">{{ trans('event.private') }}&nbsp;</label>
                        <input type="checkbox" id="is_private" style="margin-left:10px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="createNewLiveStream()" id="start-btn">{{ trans('general.home.create') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="showLiveStreamShareLinkModal" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-share-alt"></i> {{ trans('event.invitation-link') }}</h4>
                </div>
                <div class="modal-body">
                    <p>{{ trans('general.home.this-is-invitation-link-for-livestream') }}</p>
                    <div style="position: relative">
                        <textarea id="invite_link" style="width: 100%"></textarea>
                        <span style="position:absolute;top:10px;right:5px">
                            <button class="btn btn-primary" data-clipboard-target="#invite_link">
                                <i class="fa fa-clipboard"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="goToStream()" id="start-btn">{{ trans('general.home.start-now') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js" defer></script>
<script type="module">
    var stream_link = '';
    window.showNewLiveStreamModal = () => {
        $('#newLiveStream').modal('show')
    }
    window.createNewLiveStream = () => {
        if ($('#new_name').val().trim() === ''){
            alert('{{ trans("general.home.please-input-stream-name") }}');
            return;
        }
        $('#start-btn').prop('disabled', true);
        var formData = new FormData();
        formData.append('stream_name', $('#new_name').val().trim());
        formData.append('is_private', $('#is_private').is(":checked") ? 1: 0);
        formData.append('_token', "{{ csrf_token() }}");
        $.ajax({
            url : "{{ route('stream.create')}}",
            type : 'POST',
            data : formData,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            success : function(data) {
                if (data.success){
                    stream_link = 'https://call.eventssmarter.com/streams/' + data.data.id + '?{{ $login_token }}';

                    $('#invite_link').val("https://call.eventssmarter.com/streams/" + data.data.id + "?invite=" + '{{ $encrypted_login_token }}');
                    $('#newLiveStream').modal('hide');
                    new ClipboardJS('.btn');
                    $('#showLiveStreamShareLinkModal').modal('show');
                }
                else {
                    alert(data.message);
                }
            }
        });
    }

    window.goToStream = () => {
        window.location.href = stream_link;
    }

    window.deleteLiveStream = (id) => {
        if (confirm('{{ trans("general.home.are-you-sure-delete-stream") }}?')){
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_token', "{{ csrf_token() }}");
            $.ajax({
                url : "{{ route('stream.delete')}}",
                type : 'POST',
                data : formData,
                processData: false,  // tell jQuery not to process the data
                contentType: false,  // tell jQuery not to set contentType
                success : function(data) {
                    if (data.success){
                        window.location.reload()
                    }
                    else {
                        alert(data.message);
                    }
                }
            });
        }
    }

    window.getInviteLink = (stream_id, login_token, target) => {
        var textArea = document.createElement("textarea");
        textArea.value = "https://call.eventssmarter.com/streams/" + stream_id + "?invite=" + login_token;
        textArea.style.opacity = "0";
        target.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            swal({text: "{{ trans('general.home.invitation-link-copied-clipboard') }}!", icon:"success"});
        } catch (err) {
            alert('Unable to copy value , error : ' + err.message);
        }
        target.removeChild(textArea);
    }
</script>
@endsection
