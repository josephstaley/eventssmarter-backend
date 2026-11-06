@if(session()->has('stripe_approved'))
    <!-- Honey Doo, Honey Done {{ session()->forget('stripe_approved') }} -->
    <div class="modal fade" id="stripe_approved" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">All set! Now just add your events and tickets.</h4>
                </div>
                <div class="modal-body">
                    <img class="w-full" src="/images/events.jpg" alt="Get Ready for Take Off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Ok, close</button>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script type="module">
            $('#stripe_approved').modal('show')
        </script>
    @append
@endif
