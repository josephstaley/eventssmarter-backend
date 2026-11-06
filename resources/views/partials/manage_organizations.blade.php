<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-6 uppercase">
                {{trans('models.organization.organizations')}}
            </div>
            <div class="col-md-6 col-sm-6 col-xs-6">
                <a href="{{ route('organization.create') }}" class="pull-right">
                    <i class="fa fa-plus"></i> {{trans('models.organization.add')}}
                </a>
            </div>
        </div>
    </div>

    <div class="panel-body">
        @foreach($organizations as $organization)
        <div class="panel-link row">
            <div class="col-xs-10">
                <h4>
                    <a href="{{ route('organization.show',$organization) }}" class="text">
                        {{ $organization->name }}
                    </a>
                </h4>
                <span class="text-detail">
                    <a href="{{ route('organization.show',$organization) }}" class="text">
                        {{trans('general.updated')}} {{ $organization->updated_at->diffForHumans() }}
                        | {{ $organization->events()->notended()->count() }} {{trans('models.event.upcoming_events')}}
                    </a>
                </span>
            </div>
            <div class="col-xs-1" style="padding:0px">
                <a class="btn text tooltip-text" onclick="getReferralLink('{{ $organization->slug }}', this)">
                    <i class="fa fa-share-alt"></i>
                    <span class="tooltiptext">{{ trans('general.home.get-referral-link') }}</span>
                </a>
            </div>
            <div class="col-xs-1" style="padding:0px">
                <a href="{{ route('organization.edit',$organization) }}" class="btn text">
                    <i class="fa fa-ellipsis-h"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@section('scripts1')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js" defer></script>
<script type="module">
    window.getReferralLink = (slug, target) => {
        var textArea = document.createElement("textarea");
        textArea.value = "{{ env('APP_URL') }}/register?sell_tickets=true&referral=" + slug;
        textArea.style.opacity = "0";
        target.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            var successful = document.execCommand('copy');
            swal({text: "{{ trans('general.home.referral-link-copied-clipboard') }}!", icon:"success"});
        } catch (err) {
            alert('Unable to copy value , error : ' + err.message);
        }
        target.removeChild(textArea);
    }
</script>
@endsection
