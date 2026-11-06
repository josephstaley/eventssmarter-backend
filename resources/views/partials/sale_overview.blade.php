<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            {{ trans('event.sales') }} <a href="{{ route('sales.show',$event) }}" class="pull-right"><!--<i class="fa fa-cc-visa"></i>--> {{ trans('event.view-all') }}</a>
        </div>
        <div class="panel-body">
            <a href="{{ route('sales.show',$event) }}">
                @include('partials.sales_row')
            </a>
        </div>
    </div>
</div>
