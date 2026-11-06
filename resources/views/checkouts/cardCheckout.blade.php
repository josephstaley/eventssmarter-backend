@extends('layouts.app')

@section('meta')
<title>{{ trans('checkout.checkout-event') }} | {{ env('APP_NAME') }}</title>
<style>
  #pay-btn {
    width: 80px;
  }

  #card-element{
    overflow: hidden;
  }
</style>
@endsection

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3>{{ trans('checkout.credit-card-checkout') }}</h3>
        </div>
        <div class="panel-body">
          <h4>{{ trans('checkout.total-to-pay') }}: {{ $amount }} {{ $currency }}</h4>
          <br>
          <div id="card-element">
          </div>
          <button class="btn btn-primary" id="pay-btn">{{ trans('checkout.pay') }}</button>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="col-md-6 col-sm-6">
            <img src="{{ $ticket->event->image }}" style="width: 100%">
          </div>
          <div class="col-md-6 col-sm-6">
            <h3>{{ $ticket->event->name }}</h4>
            <h4>{{ $ticket->name }}</h5>
            <br/>
            <h5>{{ trans('checkout.quantity') }}: {{ $quantity }}</h5>
            <h5>{{ trans('checkout.price') }}: {{ $price }} {{ $currency }}</h5>
            <h5>{{ trans('checkout.tax') }}: {{ $tax }}</h5>
            <h4 class="ticket-total-price">
              {{ $amount }} {{ $currency }}
            </h4>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script type="module">
  const poyntCollect = document.createElement("script");
    const options = {
      iFrame: {
        width: "550px",
        height: "100px",
        border: "0px",
        "borderRadius": "4px"
      },
      displayComponents: {
        showEndingPage: false,
        submitTokenButton: false,
        submitTokenButton: false
      },
      customCss: {
        "input": {
          "base": "color: black;", // font color
          "errored": "color: maroon;",
          "cardNumber": "border-bottom: 1px;",
          "expiryDate": "text-transform: uppercase; color: green",
          "cvc": "color: purple;"
        },
      }
    };
    poyntCollect.src = "https://poynt.net/snippet/poynt-collect/bundle.js";
    poyntCollect.async = true;
    poyntCollect.parentUrl = window.location.hostname;
    poyntCollect.onload = () => {
      const collect = new TokenizeJs(
        '{{ env('POYNT_BUSINESS_ID') }}',
        "{{ env('POYNT_APP_ID') }}",
      );
      collect.mount("card-element", document, options);
      var button = $('#pay-btn')[0];
      button.addEventListener("click", event => {
        $('#pay-btn').attr('disabled', 'disabled');
        setTimeout(() => {
            if ($('#nonce').val() === ''){
                $('#pay-btn').removeAttr('disabled');
            }
        }, 2000)
        event.preventDefault();
        collect.getNonce({});
      });
      collect.on("nonce", event => {
        $('#pay-btn').attr('disabled', 'disabled');
        var formData = new FormData();
        formData.append('nonce', event.data.nonce);
        formData.append('voucher_bundle_id', "{{ $voucher_bundle_id }}");
        formData.append('_token', "{{ csrf_token() }}");
        swal({
            text: '{{ trans('checkout.processing-your-payment') }}...',
            buttons: false,
            closeOnClickOutside: false,
            icon: "info"
        })
        $.ajax({
            url : "{{ route('checkout.poynt.charge')}}",
            type : 'POST',
            data : formData,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            success : function(data) {
                if (data.success){
                  swal({
                      text: '{{ trans('checkout.your-payment-success') }}!',
                      icon: 'success',
                      buttons: false,
                      closeOnClickOutside: false,
                  })
                  setTimeout(() => {
                    window.location.href = '/tickets/{{ $ticket->event->id }}';
                  }, 1000);
                }
                else{
                  swal({
                      text: data.error,
                      icon: 'warning'
                  });
                  $('#pay-btn').removeAttr('disabled');
                }
            },
            error: function(error) {
              swal({
                  text: error.responseJSON.error,
                  icon: 'warning'
              });
              $('#pay-btn').removeAttr('disabled');
            }
        });
      });
    };
    document.body && document.body.appendChild(poyntCollect);
</script>
@endsection
