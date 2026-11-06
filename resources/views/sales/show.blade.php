@extends('layouts.app')

@section('meta')
    <style>
        @media (max-width : 580px) {
            .sales-body {
                float: left;
            }

            .sales-container {
                float: left;
            }
        }
    </style>
@endsection

@section('content')
    <div class="col-md-12">
        <a href="{{ $event->link('events.show') }}"><i class="fa fa-hand-o-left"></i> {{ trans('event.back-to-event') }}</a>
        <br><br>

        <div class="row">
            <div class="col-md-12 sales-container">
                <div class="panel panel-default sales-body">
                    <div class="panel-heading">
                        {{ trans('event.ticket-sales-for') }} "{{ $event->name }}"
                    </div>

                    <div class="panel-body">

                        <br>

                        @include('partials.sales_row')

                        <br><br>

                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('sales.exportcsv', $event) }}" target="blank">
                                    <i class="fa fa-download"></i>
                                    {{ trans('event.download-csv') }}
                                </a>
                                <a style="cursor: pointer;margin-left:30px" onclick="promoCodeShowModal()">
                                    <i class="fa fa-users"></i> {{ trans('checkout.promocode') }}
                                </a>
                            </div>
                            <form method="GET" action="{{ route('sales.show', $event) }}">
                                <div class="col-md-6" style="display: flex">
                                    <input type="hidden" value="<?php echo csrf_token(); ?>">
                                    <input class="form-control" value="{{ $search }}" name="search" placeholder="{{ trans('event.search-name-email') }}<?php if (\App\Models\User::isAdmin()) {
                                        echo ' or Transaction ID';
                                    } ?>...">
                                    <button type="submit" class="btn btn-primary" style="margin-bottom: 8px;position:absolute;right:16px;"><i class="fa fa-search"></i></button>
                                </div>
                            </form>
                        </div>

                        <div class="row">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ trans('auth.name') }}</th>
                                        <th>{{ trans('auth.email') }}</th>
                                        <th>{{ trans('checkout.price') }}</th>
                                        <th>{{ trans('checkout.promocode') }}</th>
                                        <th>{{ trans('organization.affiliate') }} {{ trans('checkout.amount') }}</th>
                                        <th>{{ trans('event.view-ticket') }}</th>
                                        <th>{{ trans('event.purchased-date') }}</th>
                                        <th>{{ trans('event.scanned-date') }}</th>
                                        @if (\App\Models\User::isAdmin())
                                            <th>Transaction ID</th>
                                        @endif
                                        <th>{{ trans('checkout.address') }}</th>
                                        <th>{{ trans('checkout.employer') }}</th>
                                        <th>{{ trans('checkout.occupation') }}</th>
                                        @if (\App\Models\User::isAdmin())
                                            <th></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sales as $i => $s)
                                        <tr>
                                            <td>{{ $s->name }}</td>
                                            <td>{{ $s->email }}</td>
                                            <td>{{ $s->detail_amount > 0 ? $currency['symbol'] . ' ' . ($currency['zero_decimal'] ? number_format($s->detail_amount, 2) : number_format($s->detail_amount / 100, 2)) : $currency['symbol'] . ' 0' }}</td>
                                            <td>{{ \App\Models\PromoCode::withTrashed()->where('id', $s->promo_code_org_id)->first()? \App\Models\PromoCode::withTrashed()->where('id', $s->promo_code_org_id)->first()->promo_code: '' }}</td>
                                            <td>{{ $s->affiliate_status ? $currency['symbol'] . ' ' . ($currency['zero_decimal'] ? $s->sent_affiliate_amount : $s->sent_affiliate_amount / 100) : $currency['symbol'] . ' 0' }}</td>
                                            <td>
                                                <a href="{{ route('voucher.download', $s->voucher_id) }}">
                                                    {{ $s->ticket_name }} | {{ $s->detail_text }}</a>
                                            </td>
                                            <td>{{ date_format(date_create($s->purchased_date), ' h:i A, l, M d, Y') }}</td>
                                            <td>{{ $s->used_at ? date_format(date_create($s->used_at), ' h:i A, l, M d, Y') : '' }}</td>
                                            @if (\App\Models\User::isAdmin())
                                                <td><span style="word-break: break-all"> {{ strpos($s->charge_id, '-') !== 0 ? $s->charge_id : '' }} </span></td>
                                            @endif
                                            <td><span style="word-break: break-all"> {{ $s->address }} </span></td>
                                            <td><span style="word-break: break-all"> {{ $s->employer }} </span></td>
                                            <td><span style="word-break: break-all"> {{ $s->occupation }} </span></td>
                                            @if (\App\Models\User::isAdmin())
                                                <td>
                                                    @if ($s->is_hide_on_sales !== 1)
                                                        <a href="{{ route('voucher.hideSales', $s->voucher_id) }}">Hide</a>
                                                    @else
                                                        <a href="{{ route('voucher.showSales', $s->voucher_id) }}">Show</a>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

                {{ $sales->render() }}

                <div class="modal fade" id="promoShowModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ trans('event.promo-codes-bought-tickets') }}</h4>
                            </div>
                            <div class="modal-body">
                                <table class="table" id="upcomingLiveStreamsTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ trans('event.no') }}</th>
                                            <th class="text-center">{{ trans('checkout.promocode') }}</th>
                                            <th class="text-center">{{ trans('event.count') }}</th>
                                            <th class="text-center">{{ trans('checkout.amount') }}</th>
                                            <th class="text-center">{{ trans('auth.email') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($promo_codes as $key => $item)
                                            <tr>
                                                <td class="text-center">{{ $key + 1 }}</td>
                                                <td class="text-center">{{ $item->promo_code }}</td>
                                                <td class="text-center">{{ $item->count }}</td>
                                                <td class="text-center">{{ number_format($item->sum_amount * 0.01, 2) }}</td>
                                                <td class="text-center">{{ $item->affiliate_email }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('event.close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="module">
    window.promoCodeShowModal = () => {
        $('#promoShowModal').modal('show')
    }
</script>
@endsection
