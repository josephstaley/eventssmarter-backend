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
        'title': 'Payment needs Verification!',
        'text' : "Your Order ID is <?php echo $order_id; ?>. \n You should verify your transaction.\nAfter verification, we will send ticket receipt email. \n If you have problem with this, please contact us with your Order ID.",
        'icon': 'warning'
    }).then(() => {
        window.location.href = '/tickets';
    })
</script>
@endsection
