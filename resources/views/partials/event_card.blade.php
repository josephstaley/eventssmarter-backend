<div class="col-md-4 col-sm-6">
    <div class="event-card">
        <div class="image">
            <a href="{{ $event->link('events.show') }}">
                <img src="{{ $event->image }}" alt="" class="img-responsive">
            </a>
        </div>
        <div class="name">
            <a href="{{ $event->link('events.show') }}">
                {{ $event->name }}
            </a>
        </div>
        <div class="details">
            <div class="col-md-3 col-sm-3" style="padding-left: 0px">
                <a class="category btn btn-primary w" href="/organizations/{{ lcfirst($event->organization()->first()->category) }}">{{ $event->organization()->first()->category }}</a>
            </div>
            <div class="col-md-9 col-sm-9 text-right">
                <i class="date fa fa-calendar"></i>
                <span class="date">
                    @if(date_format($event->starts_at, 'F d, Y') !== date_format($event->ends_at, 'F d, Y'))
                    {{ date_format($event->starts_at, 'F d')}} ~ {{ date_format($event->ends_at, 'F d, Y')}}</span>
                    @else
                    {{ date_format($event->starts_at, 'F d, Y')}}
                    @endif
                <br/>
                <i class="organization fa fa-briefcase"></i>
                {{ $event->organization()->first()->name }}
            </div>
        </div>
        <div class="description">
            {{ $event->description }}
        </div>
    </div>
</div>