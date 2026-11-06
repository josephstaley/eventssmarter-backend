<div class="row text-center">
    <div class="col-md-6 col-sm-6">
        <h2><i class="fa fa-ticket"></i></h2>
        <h2>{{ $event->sales()->sold }}</h2>
        <h4>{{ $event->sales()->tickets }} {{ str_plural(trans('event.ticket'), $event->sales()->tickets) }}</h4>
    </div>
    {{-- <div class="col-md-4">
        <h2><i class="fa fa-hand-o-down"></i></h2>
        <h2>{{ $event->sales()->refunds_total }}</h2>
        <h4>{{ $event->sales()->refunds }} {{ str_plural('Refund',$event->sales()->refunds) }}</h4>
    </div> --}}
    <div class="col-md-6 col-sm-6">
        <h2><i class="fa fa-user"></i></h2>
        <h2>{{ $event->sales()->attendees }} {{ trans('event.people') }}</h2>
        <h4>{{ trans('event.attended') }}</h4>
    </div>
</div>
