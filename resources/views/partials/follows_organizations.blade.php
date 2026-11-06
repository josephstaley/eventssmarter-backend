<div class="panel panel-default">
    <div class="panel-heading">
        <div class="row">
            <div class="col-md-6 uppercase">
                {{trans('models.organization.follow')}}
            </div>
        </div>
    </div>

    <div class="panel-body">
        @if($follows->isEmpty())
            {{trans('models.organization.no_follows')}}, <a href="{{ route('organization.index') }}">{{trans('general.click_here')}}</a> {{trans('models.organization.find_follow')}}
        @endif
        @foreach($follows as $organization)
            <div class="panel-link row">
                <div class="col-xs-11">
                    <h4>
                        <a href="{{ route('organization.show',$organization) }}" class="text">
                            {{ $organization->name }}
                        </a>
                    </h4>
                    <span class="text-detail">
                                        <a href="{{ route('organization.show',$organization) }}" class="text">
                                            {{trans('general.updated')}} {{ $organization->updated_at->diffForHumans() }}
                                            | {{ $organization->events()->upcoming()->count() }} {{trans('models.event.upcoming_events')}}
                                        </a>
                                    </span>
                </div>
                <div class="col-xs-1">
                    <a href="{{ route('organization.show',$organization) }}" class="btn text">
                        <i class="fa fa-ellipsis-h"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>