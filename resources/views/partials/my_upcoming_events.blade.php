@if($myEvents && !$myEvents->isEmpty())
    <div class="panel panel-default">
        <div class="panel-heading">
            {{trans('models.event.my_upcoming_events')}}
        </div>

        <div class="panel-body">

            @foreach($myEvents as $event)
                <div class="panel-link">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <img src="{{ $event->image }}" alt="{{ $event->name }}" class="event-img">
                        </div>
                        <div class="col-md-6">
                            <h4>
                                <a href="{{ route('vouchers.show',[$event->id]) }}" class="text">
                                    {{ $event->name }}
                                </a>
                            </h4>
                            <span class="text-detail event-description">
                                {{ $event->description }}
                                <br>
                                {{trans('models.ticket.starts')}} {{ $event->starts_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('vouchers.show',[$event->id]) }}" class="btn btn-default">
                                {{trans('models.ticket.view_tickets')}}
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