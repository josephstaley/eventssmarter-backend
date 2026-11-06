@extends('layouts.app')

@section('meta')
<title>{{ trans('organization.affiliate-organization-bank-information') }} | {{ env('APP_NAME') }}</title>
<meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
@endsection

@section('content')
<div class="container">
    <h3>
        {{ trans('organization.you-are-registered-as-affiliate-org') }}: <a href="{{ route('organization.show', $organization) }}">{{ $organization->name }}</a>
    </h3>
    <br>
    <p>
        {{ trans('organization.your-promocode') }}: <span style="color:blue">{{ $promoCode->promo_code }}</span>
    </p>
    <hr>
    <p>
        {{ trans('organization.your-bank-information-for-payout') }}
    </p>
    <form class="input-bank-info row form-group" action={{ route('organization.affiliate.submit', [$organization, $promoCode]) }} method="POST">
        {{ csrf_field() }}
        <div class="col-sm-3 col-md-3">
            <span>{{ trans('checkout.first-name') }}</span>
            <input type="text" value="{{ $promoCode->bank_first_name }}" class="form-control" name="bank_first_name" placeholder="{{ trans('checkout.first-name') }}" required/>
        </div>
        <div class="col-sm-3 col-md-3">
            <span>{{ trans('checkout.last-name') }}</span>
            <input type="text" value="{{ $promoCode->bank_last_name }}" class="form-control" name="bank_last_name" placeholder="{{ trans('checkout.last-name') }}" required />
        </div>
        <div class="col-sm-3 col-md-3">
            <span>{{ trans('organization.business-name') }}</span>
            <input type="text" value="{{ $promoCode->bank_business_name }}" class="form-control" name="bank_business_name" placeholder="{{ trans('organization.business-name') }}" required />
        </div>
        <div class="col-sm-3 col-md-3">
            <span>{{ trans('organization.business-email') }}</span>
            <input type="text" value="{{ $promoCode->bank_business_email }}" class="form-control" name="bank_business_email" placeholder="{{ trans('organization.business-email') }}" required />
        </div>
        <div class="col-sm-6 col-md-6">
            <span>{{ trans('organization.bank-routing-number') }}</span>
            <input type="text" value="{{ $promoCode->bank_routing_number }}" style="margin-right:10px;" class="form-control" name="bank_routing_number" placeholder="{{ trans('organization.bank-routing-number') }}" required />
        </div>
        <div class="col-sm-6 col-md-6">
            <span>{{ trans('organization.bank-account-number') }}</span>
            <input type="text" value="{{ $promoCode->bank_account_number }}" class="form-control" name="bank_account_number" placeholder="{{ trans('organization.bank-account-number') }}" required/>
        </div>
        <div class="col-md-12 col-sm-12 text-center">
            <button class="btn btn-primary" type="submit">{{ trans('organization.update') }}</button>
        </div>
    </form>
</div>
@endsection
@if(isset($success))
@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script type="module">
    swal("Success", "{{ trans('organization.your-information-has-been-updated') }}!", "success");
</script>
@endsection
@endif
