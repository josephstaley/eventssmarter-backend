@if(session()->has('message'))

    <div style="position:absolute;min-width: 300px" id="alert_message">
        <div class="alert alert-{{ session('type') ?? 'success' }}">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>{{ session('alert') ?? 'Sweet!' }}</strong> {!! session('message') !!}
        </div>
    </div>

@section('scripts')
        <script type="module">
            setTimeout(function(){
                $('#alert_message').fadeOut();
            },3000);
        </script>
    @append
@endif
