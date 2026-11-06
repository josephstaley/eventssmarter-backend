@if($pastEvents && !$pastEvents->isEmpty())
    <div class="panel panel-default">
        <div class="panel-heading">
            {{trans('models.event.my_past_events')}}
        </div>

        <div class="panel-body">

            @foreach($pastEvents as $event)
                <div class="panel-link">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <img src="{{ $event->image }}" alt="{{ $event->name }}" class="event-img">
                        </div>
                        <div class="col-md-8">
                            <h4>
                                <a  href="{{ $event->link('events.show') }}" class="text">
                                    {{ $event->name }}
                                </a>
                            </h4>
                            <span class="text-detail event-description">
                                {{ $event->description }}
                                <br>
                                {{trans('models.ticket.starts')}} {{ $event->starts_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="col-xs-1">
                            <a href="{{ route('events.edit', [$event->organization, $event->id]) }}" class="btn text">
                                <i class="fa fa-ellipsis-h"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
@else
    <div class="well text-center">
        <h3 class="title">{{trans('models.ticket.no_ticket')}}</h3>
        <br>
        <p>{{trans('models.ticket.go_to_the')}} <a href="/events">{{trans('models.event.events_page')}}</a> {{trans('models.ticket.right_for_you')}}!</p>
    </div>
@endif