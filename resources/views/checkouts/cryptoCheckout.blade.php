@extends('layouts.app')

@section('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trans('checkout.crypto') }} {{ trans('checkout.checkout') }} | {{ env('APP_NAME') }}</title>
    <style>
        .event-image {
            margin-left: 20%;
            margin-right: 20%;
            width: 60%;
            margin-bottom: 30px;
        }

        .amount {
            margin-top: 10px;
        }

        .crypto-rates {
            font-size: 9pt;
            color: #aaa;
            margin-top: 10px;
        }

        .crypto-amount {
            margin-top: 1px;
            margin-left: 17px;
        }

        .etherum-icon {
            margin-top: -6px;
        }

        .pay-btn {
            width: 80px;
        }
    </style>
    <script type="text/javascript" src="https://unpkg.com/jquery@3.3.1/dist/jquery.js" defer></script>
    <script type="text/javascript" src="https://unpkg.com/web3@1.7.0/dist/web3.min.js" defer></script>
@endsection

@section('content')
    <div class="container col-md-8 col-sm-12">
        <div class="row">
            <div class="col-md-6">
                <h5> {{ trans('checkout.pay-via-crypto') }}</h5>
                <h3 class="amount"> {{ $amount }} {{ $ticket->event->organization->currencySymbol }}</h3>
                <img src="{{ $ticket->event->image }}" alt="" srcset="" class="event-image">
            </div>
            <div class="col-md-6">
                <h3>{{ $ticket->event->name }} {{ $ticket->name }}</h3>
                <br />
                <br />
                <div class="flex">
                    <h4><strong>{{ trans('checkout.amount') }}</strong>:</h4>
                    <span class="crypto-amount">{{ (1 / $crypto_rates) * $amount }} ETH <img style="height: 33px" class="etherum-icon" src="/images/crypto/etherum_no_border.svg"></span>
                </div>
                <p class="crypto-rates">1 ETH = {{ $crypto_rates }} {{ $ticket->event->organization->currency }}</p>
                <br />
                <button class="btn btn-primary pay-btn" onclick="pay()" id="pay-btn">{{ trans('checkout.pay') }}</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts1')
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
    <script type="module">
    $(document).ready(() =>{
        if (window.ethereum) {
            window.web3 = new Web3(window.ethereum);
            window.ethereum.enable();
        }
    });
    window.pay = async () => {
        try {
            if (window.ethereum) {
                window.contract = await new window.web3.eth.Contract({!! config('crypto.abi') !!}, '{{ config('crypto.smartcontract_address') }}');
                const accounts = await window.web3.eth.getAccounts();
                if (accounts.length > 0){
                    const account = accounts[0];
                    const wei = window.web3.utils.toWei('{{ 1 / $crypto_rates * $amount }}', 'ether');
                    swal({
                        text: '{{ trans('checkout.processing-your-payment') }}...',
                        buttons: false,
                        closeOnClickOutside: false,
                        icon: "info"
                    })
                    window.contract.methods.buyItem(wei, {{ $ticket->event->organization->app_fee_percentage }},'{{ $ticket->event->organization->wallet_address }}', {{ \json_encode($voucher_bundle_ids) }})
                    .send({
                        from: account,
                        value: wei,
                    })
                    .on('error', function (error) {
                        window.web3.eth.net.getNetworkType().then(res =>{
                            if (res !== '{!! config("crypto.network") !!}'){
                                swal({
                                    text: '{{ trans('checkout.please-change-your-wallet-network') }}',
                                    icon: "info"
                                });
                            }
                            else {
                                swal({
                                    text: error.message,
                                    icon: "info"
                                });
                            }
                        })
                    })
                    .then(res =>{
                        $('#pay-btn').attr('disabled' , true);
                        swal({
                            title: '{{ trans('checkout.payment-confirmed') }}!',
                            text: '{{ trans('checkout.your-transaction-hash-is') }} ' + res.transactionHash,
                            icon: 'success'
                        }).then(() => {
                            swal({
                                text: '{{ trans('checkout.payment-is-verifying') }}..',
                                icon: 'info',
                                buttons: false,
                                closeOnClickOutside: false,
                            })
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });
                            $.ajax({
                                type:'POST',
                                url: '/api/crypto/paymentVerify',
                                data:{
                                    voucher_ids: {{ \json_encode($voucher_bundle_ids) }},
                                    hash: res.transactionHash,
                                    amount: '{{ 1 / $crypto_rates * $amount }}'
                                },
                                success:function(res){
                                    res = JSON.parse(res);
                                    if(res.success){
                                        swal({
                                            text: '{{ trans('checkout.payment-is-verified') }}!',
                                            icon: 'info',
                                            buttons: false,
                                            closeOnClickOutside: false,
                                        })
                                        window.location.href = '/tickets/{{ $ticket->event->id }}';
                                    }
                                    else {
                                        swal({
                                            text: res.message,
                                            icon: 'warning'
                                        });
                                    }
                                },
                                error: function(err){
                                    swal({
                                        text: err,
                                        icon: 'warning'
                                    })
                                }
                            });
                        })
                    })
                }
                else {
                    swal({
                        text: '{{ trans('checkout.please-connect-your-wallet') }}',
                        icon: 'warning'
                    })
                }
            }
        } catch (error) {
            console.log(error)
            swal({
                text: error,
                icon: 'warning'
            })
        }
    }
</script>
@endsection
