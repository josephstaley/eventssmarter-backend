@if(request('seller_registered'))
    <div class="modal fade" id="seller_registered" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">You signed up as a ticket seller (Step 2 of 4)</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <img src="/images/lineup.jpg" alt="Get Ready for Take Off" style="height: 120px">
                    </div>
                    <div class="text-center">
                        <h5 style="font-weight: bolder">You're 2 steps away from selling tickets!</h5>
                        <p class="text-muted text-left" style="max-width: 500px; margin: 0 auto;">
                            We call ticket sellers "Organizations", eventually you can invite friends, coworkers, etc to
                            help manage your organizations, but for right now let's fill out some base information
                            start with your organization's name and location, and if your feeling really sharey, you
                            can set up social share icons. But don't worry those aren't required.
                        </p>
                        <br>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Ok, close</button>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script type="module">
            $('#seller_registered').modal('show')
        </script>
    @append
@endif
