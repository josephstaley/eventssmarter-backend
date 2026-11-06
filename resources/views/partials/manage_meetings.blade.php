<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-6 uppercase">
                {{ trans('general.home.my-meetings') }}
            </div>
            <div class="col-md-6 col-sm-6 col-xs-6">
                <a class="pull-right" onclick="showNewMeetingModal()" style="cursor: pointer">
                    <i class="fa fa-video-camera"></i> {{ trans('general.home.new-meeting') }}
                </a>
            </div>
        </div>
    </div>

    <div class="panel-body">
        @foreach($meetings as $item)
            <div class="panel-link row">
                <div class="col-xs-10">
                    <h4>
                        <a href="https://meet.eventssmarter.com/{{ $item->host_url }}" class="text">
                            {{ $item->name }}
                        </a>
                    </h4>
                </div>
                <div class="col-xs-1" style="padding:0px">
                    <a class="btn text tooltip-text" onclick="getMeetingInviteLink('{{ $item->attendee_url }}', this)">
                        <i class="fa fa-share-alt"></i>
                        <span class="tooltiptext">{{ trans('event.get-invitation-link') }}</span>
                    </a>
                </div>
                <div class="col-xs-1" style="padding:0px">
                    <a class="btn text" onclick="deleteMeeting({{ $item->id }})">
                        <i class="fa fa-trash-o"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="modal fade" id="newMeeting" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-video-camera"></i> {{ trans('general.home.new-meeting') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group flex">
                        <label for="new_meeting_name" class="col-md-2 col-sm-2 col-xs-2" style="margin-top: 7px;text-align:right;">{{ trans('auth.name') }}</label>
                        <div class="col-md-9 col-sm-9 col-xs-9">
                            <input class="form-control" id="new_meeting_name">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="createNewMeeting()" id="start-btn">{{ trans('general.home.create') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="MeetingShareLinkModal" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="window.location.reload()"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-share-alt"></i> {{ trans('event.invitation-link') }}</h4>
                </div>
                <div class="modal-body">
                    <p>{{ trans('general.home.this-invitation-link-for-meeting') }}</p>
                    <div style="position: relative">
                        <textarea id="invite_meeting_link" style="width: 100%"></textarea>
                        <span style="position:absolute;top:10px;right:5px">
                            <button class="btn btn-primary" data-clipboard-target="#invite_meeting_link">
                                <i class="fa fa-clipboard"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="goToMeeting()" id="start-btn">{{ trans('general.home.start-meeting') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts3')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js" defer></script>
<script type="module">
    var meeting_link = '';
    window.showNewMeetingModal = () => {
        $('#newMeeting').modal('show')
    }
    window.createNewMeeting = () => {
        if ($('#new_meeting_name').val().trim() === ''){
            alert('{{ trans("general.home.please-input-meeting-name") }}');
            return;
        }
        $('#start-btn').prop('disabled', true);
        var formData = new FormData();
        formData.append('meeting_name', $('#new_meeting_name').val().trim());
        formData.append('_token', "{{ csrf_token() }}");
        $.ajax({
            url : "{{ route('meeting.create')}}",
            type : 'POST',
            data : formData,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            success : function(data) {
                if (data.success){
                    meeting_link = 'https://meet.eventssmarter.com/' + data.data.host_url;

                    $('#invite_meeting_link').val("https://meet.eventssmarter.com/" + data.data.attendee_url);
                    $('#newMeeting').modal('hide');
                    new ClipboardJS('.btn');
                    $('#MeetingShareLinkModal').modal('show');
                }
                else {
                    alert(data.message);
                }
            }
        });
    }

    window.goToMeeting = () => {
        window.location.href = meeting_link;
    }

    window.deleteMeeting = (id) => {
        if (confirm('{{ trans("general.home.are-you-sure-delete-meeting") }}?')){
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_token', "{{ csrf_token() }}");
            $.ajax({
                url : "{{ route('meeting.delete')}}",
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

    window.getMeetingInviteLink = (meeting_id, target) => {
        var textArea = document.createElement("textarea");
        textArea.value = "https://meet.eventssmarter.com/" + meeting_id;
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
