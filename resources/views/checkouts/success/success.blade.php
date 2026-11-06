@extends('layouts.app')

@section('meta')
<title>Checkout Event | {{ env('APP_NAME') }}</title>
<style>
    .footer {
        bottom: 0px;
        position: absolute;
        width: 100%;
    }
</style>
@endsection

@section('content')
@endsection

@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
<script type="module">
    swal({
        'title': 'Payment Success!',
        'text' : "Your Order ID is <?php echo $order_id; ?>. \n You will receive ticket receipt email soon. \n If you don't receive your receipt email, please contact with this order ID.",
        'icon': 'success'
    })
    .then(() => {
        window.location.href = '/tickets/<?php echo $event_id; ?>';
    })
</script>
@endsection
