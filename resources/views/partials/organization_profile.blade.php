<div class="col-md-3 col-md-pull-9">
    <div class="panel panel-default">
        {!! \App\Models\User::followHtml($organization) !!}
        <div class="panel-body text-center">
            <img class="profile-img" src="{{ $organization->cover_image }}" alt="{{ $organization->name }}">
            <div class="profile-title">
                <a href="{{ route('organization.show', $organization) }}">
                    <h4>{{ $organization->name }}</h4>
                </a>
            </div>
            @if ($organization->is_subscription)
                @auth
                    @if (\App\Models\User::subscribed($organization))
                        <button onclick="unsubscribe()" class="btn btn-primary" style="margin-top: 20px">{{ trans('event.unsubscribe') }}</button>
                    @else
                        <button onclick="showPlanModal()" class="btn btn-primary" style="margin-top: 20px">{{ trans('event.subscribe') }}</button>
                    @endif
                @else
                    <a class="btn btn-primary" style="margin-top: 20px" {!! \App\Models\Helpers::href('login') !!}>{{ trans('event.subscribe') }}</a>
                @endauth
            @endif

            <div class="organization profile-links">
                @isset($organization->linkedin_link)
                    <a href="{{ $organization->linkedin_link }}" target="_blank">
                        <i class="fa fa-linkedin"></i>
                    </a>
                @endisset
                @isset($organization->facebook_link)
                    <a href="{{ $organization->facebook_link }}" target="_blank">
                        <i class="fa fa-facebook"></i>
                    </a>
                @endisset
                @isset($organization->twitter_link)
                    <a href="{{ $organization->twitter_link }}" target="_blank">
                        <i class="fa fa-twitter"></i>
                    </a>
                @endisset
                @isset($organization->instagram_link)
                    <a href="{{ $organization->instagram_link }}" target="_blank">
                        <i class="fa fa-instagram"></i>
                    </a>
                @endisset
                @isset($organization->website_link)
                    <a href="{{ $organization->website_link }}" target="_blank">
                        <i class="fa fa-link"></i>
                    </a>
                @endisset
                @isset($organization->mail_link)
                    <a href="mailto:{{ $organization->mail_link }}">
                        <i class="fa fa-envelope"></i>
                    </a>
                @endisset
            </div>
            <div class="event-share-links">
                @isset($event)
                    <div class="fb-share-button" data-href="{{ $event->link('events.show') }}" data-layout="button" data-size="small">
                        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ $event->link('events.show') }}&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">
                            <i class="fa fa-share-alt"></i>
                            {{ trans('organization.share') }}
                        </a>
                    </div>
                @endisset
            </div>
            <br>
            <div class="text-left col-md-offset-2 col-md-10">
                @can('update', $organization)
                    <p>
                        <a href="{{ route('organization.edit', $organization) }}">
                            <i class="fa fa-pencil"></i>
                            {{ trans('organization.edit-details') }}
                        </a>
                    </p>
                    <p>
                        <a href="{{ route('events.create', $organization) }}">
                            <i class="fa fa-plus"></i>
                            {{ trans('organization.add-another-event') }}
                        </a>
                    </p>
                    <p>
                        <a href="javascript: void(0)" onclick="event_duplicate()" style="margin-top: 20px">
                            <i class="fa fa-copy"></i>
                            {{ trans('organization.duplicate-event') }}
                        </a>
                    </p>
                @endcan
                @if (\App\Models\User::isAdmin())
                    <br>
                    <a href="?admin_edit={{ session('admin_edit') ? 'false' : 'true' }}">
                        <i class="fa fa-user-circle"></i>
                        {{ session('admin_edit') ? 'Exit Admin Edit' : 'Admin Edit' }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    @can('update', $organization)
        <div class="panel panel-default">
            <div class="panel-body text-center">
                <h4>{{ trans('organization.ticket-scanner-app') }}</h4>
                <br>
                <form method="POST" action="{{ route('organization.change', $organization) }}" id='change-passcode-form'>
                    <h6>{{ trans('organization.organization-passcode') }}:</h6>
                    <kbd>{{ $organization->password }}</kbd>
                    @csrf
                    (<a href="javascript:;" onclick="changePasscode()">
                        {{ trans('organization.change') }} <i class="fa fa-pencil-square-o"></i>
                    </a>)
                </form>
                <br>
                <p><a href="?show_code=true">{{ trans('organization.how-do-use-this') }}?</a></p>
                <div class="text-center">
                    @include('partials.app_links')
                </div>
            </div>
        </div>
    @endcan

    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h4>{{ trans('organization.life-in-motion') }}</h4>
            <div class="text-center">
                <a href="{{ isset($event) && $event->ad_link ? $event->ad_link : $organization->ad_link }}" target="_blank">
                    <img src="{{ isset($event) && $event->ad_image ? $event->ad_image : $organization->ad_image }}" alt="Buy your Athletic Gear at {{ env('APP_NAME') }}" style="width: 120px">
                </a>
            </div>
            @if (\App\Models\User::isAdmin())
                <br>
                @if (isset($event))
                    <a href="{{ route('events.ad.edit', $event->id) }}">{{ trans('event.update-event-ad') }}</a>
                    <br>
                @endif
                <a href="{{ route('organization.ad.edit', $organization->id) }}">{{ trans('organization.update-organization-ad') }}</a>
            @endif
            <div class="text-center">
                <a href="{{ isset($event) && $event->ad_link ? $event->ad_link : $organization->ad_link }}" target="_blank" class="btn btn-primary o">{{ trans('organization.click-to-shop') }}</a>
            </div>
        </div>
    </div>
    @if ($organization->is_subscription)
        <div class="modal fade" id="subscribe_modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document" style="max-width: ">
                <div class="modal-content">
                    {{ html()->form('POST', route('organization.subscribe', [$organization]))->open() }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{ trans('organization.please-choose-your-plan') }}</h4>
                    </div>
                    <div class="modal-body row">
                        <p class="col-md-12" style="font-size: 13pt">
                            {{ trans('organization.you-can-get-free-tickets-this-organization') }}. <br />
                            {{ trans('organization.all-sales-are-final-no-refunds') }}.
                        </p>
                        <div class="col-md-4 col-sm-4 col-md-offset-2 col-sm-offset-2" style="margin-top: 20px">
                            <div id="monthly_sub" onclick="monthly_sub_click()" class="text-center border-radius border-radius-focus" style="cursor:pointer;max-width:300px;margin:auto;padding:10px">
                                <h4>{{ trans('organization.monthly') }}</h4>
                                <p style="padding-top: 20px">{{ $organization->monthly_amount() > 0 ? $organization->getCurrencySymbolAttribute() . ' ' . $organization->monthly_amount() . ' / m' : trans('event.free') }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4" style="margin-top: 20px">
                            <div id="yearly_sub" onclick="yearly_sub_click()" class="text-center border-radius" style="cursor:pointer;max-width:300px;margin:auto;padding:10px">
                                <h4>{{ trans('organization.annual') }}</h4>
                                <p style="padding-top: 20px">{{ $organization->yearly_amount() > 0 ? $organization->getCurrencySymbolAttribute() . ' ' . $organization->yearly_amount() . ' / y' : trans('event.free') }}</p>
                            </div>
                        </div>
                        <input type="hidden" name="subscription_mode" id="subscription_mode">
                    </div>
                    <div class="modal-footer">
                        {{ html()->button('Subscribe', 'submit')->class('btn btn-primary') }}
                    </div>
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
        @section('scripts1')
            <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
            <script type="module">
                window.showPlanModal = () => {
                    $('#subscribe_modal').modal('show');
                    $('#subscription_mode').val('month');
                }

                window.monthly_sub_click = () => {
                    $('#monthly_sub').addClass('border-radius-focus');
                    $('#yearly_sub').removeClass('border-radius-focus');
                    $('#subscription_mode').val('month');
                }

                window.yearly_sub_click = () => {
                    $('#yearly_sub').addClass('border-radius-focus');
                    $('#monthly_sub').removeClass('border-radius-focus');
                    $('#subscription_mode').val('year');
                }

                window.unsubscribe = () => {
                    swal({
                        title: '{{ trans("organization.unsubscribe-from") ." ". $organization->name }}',
                        text: "{{ trans('organization.are-you-sure-unsubscribe') }}?",
                        showCancelButton: true,
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancel',
                        allowOutsideClick: false,
                        icon: "warning",
                        buttons: true
                    }).then((result) => {
                        if (result) {
                            swal({
                                title: "{{ trans('event.please-wait') }}",
                                text: "{{ trans('organization.processing-unsubscribe-now') }}...",
                                buttons: false,
                                closeOnClickOutside: false,
                                icon: "info"
                            });
                            $.ajax({
                                type: 'POST',
                                url: "{{ route('organization.unsubscribe', ['organization' => $organization->id]) }}",
                                data: {
                                    organization: {{ $organization->id }}
                                },
                                success: function(data) {
                                    if (data.success) {
                                        swal({
                                            text: "{{ trans('organization.you-unsubscribe-successfully') }}!",
                                            icon: "success",
                                            allowOutsideClick: false
                                        }).then(() => {
                                            window.location.reload();
                                        })
                                    }
                                },
                                error: function() {
                                    window.location.reload();
                                }
                            });
                        }
                    });
                }
            </script>
        @endsection
    @endif
    @if (isset($event))
        @section('scripts2')
            <script type="module">
                window.changePasscode = () => {
                    swal({
                        text: "{{ trans('organization.are-you-sure-change-password') }}",
                        showCancelButton: true,
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancel',
                        allowOutsideClick: false,
                        icon: "warning",
                        buttons: true
                    }).then((result) => {
                        if (result) {
                            $('#change-passcode-form').submit();
                        }
                    });
                }
                window.event_duplicate = () => {
                    swal({
                        title: '{{ trans("organization.duplicate-event") ." ". $event->name }}',
                        text: "{{ trans('organization.are-you-sure-duplicate') }}?",
                        showCancelButton: true,
                        confirmButtonText: 'OK',
                        cancelButtonText: 'Cancel',
                        allowOutsideClick: false,
                        icon: "warning",
                        buttons: true
                    }).then((result) => {
                        if (result) {
                            swal({
                                title: "{{ trans('event.please-wait') }}",
                                text: "{{ trans('organization.processing-duplicate-now') }}...",
                                buttons: false,
                                closeOnClickOutside: false,
                                icon: "info"
                            });
                            $.ajax({
                                type: 'POST',
                                url: "{{ route('events.duplicate', ['organization' => $organization, 'id' => $event->id]) }}",
                                success: function(data) {
                                    if (data.success) {
                                        window.location.href = data.link;
                                    }
                                },
                                error: function() {
                                    // window.location.reload();
                                }
                            });
                        }
                    });
                }
            </script>
        @endsection
    @endif

</div>
