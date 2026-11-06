@if (request('show_code'))
    <div class="modal fade" id="gettheapp" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">{{ trans('general.modal.great-tickets-were-added-event') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <h2>{{ trans('general.modal.now-get-the-ticket-scanner-app') }}!</h2>
                        <h4>{{ trans('general.modal.download') }} "{{ env('APP_NAME') }}" {{ trans('general.modal.in-the-app-store') }}</h4>
                        <img src="/images/scan.jpg" alt="Scan tickets with the {{ env('APP_NAME') }} app">
                    </div>
                    <p>{{ trans('general.modal.using-your-passcode') }}
                        <kbd>{{ $organization->password }}</kbd>
                        {{ trans('general.modal.you-can-allow-app-verify-ticket') }}.
                    </p>
                    <p>{{ trans('general.modal.download-it-today') }}</p>
                    <div class="text-center">
                        @include('partials.app_links')
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('event.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @section('scripts1')
        <script type="module">
        var showcode = localStorage.getItem('showcode');
        if (!showcode){
            $('#gettheapp').modal('show');
            localStorage.setItem('showcode', 1)
        }
    </script>
    @append
@endif
