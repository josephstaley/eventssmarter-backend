@isset($organization)
    @can('update', $organization)
        @if (!$organization->stripe_account)
            <div class="modal fade" id="payment_modal" tabindex="-1" role="dialog" data-backdrop="static">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Great! Now let's Setup Payments</h4>
                        </div>
                        <div class="modal-body">
                            <div class="text-center">
                                <img src="/images/payment.jpg" alt="Setup a way to take money">
                            </div>
                            <br>
                            <div class="text-center">
                                <h5 style="font-weight: bolder">Connect your account to Stripe</h5>
                                <p class="text-muted" style="max-width: 300px; margin: 0 auto;">
                                    We use Stripe to make sure you get paid on time and keep your personal and bank
                                    details
                                    secure.
                                </p>
                                <br>
                                <a href="{{ $organization->signupURL() }}" id="connect_logo" onclick="$(this).html('Loading...')">
                                    <img src="/images/connect.png" alt="Connect on Stripe">
                                </a>
                                <br><br>
                            </div>
                            <div class="modal-footer">
                                <a href="{{ $organization->signupURL() }}" onclick="$('#connect_logo').html('Loading...');" class="btn btn-default">Ok, let's go!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @section('scripts')
                @if (Session::has('ticket-create') || Session::has('ticket-update'))
                    <script>
                        //$('#payment_modal').modal('show')
                    </script>
                @endif
            @append
        @endif
    @endcan
@endisset
