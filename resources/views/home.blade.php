@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.menu.dashboard') }} | {{ env('APP_NAME') }}</title>
    <meta name="description" content="Dashboard">
    <meta name="keywords" content="Dashboard">
    <meta property="og:title" content="{{ env('APP_NAME') }} Dashboard" />
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
    <meta property="og:description" content="Manage your events" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .paginate_button {
            margin-right: 10px;
            cursor: pointer;
        }

        .subscription-item {
            margin-bottom: 10px;
        }

        .subscription-item a {
            display: inline-block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 30ch;
            padding-top: 10px;
        }

        .referral-monthly-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between
        }
    </style>
    @if ($user->organization_enabled)
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    @endif
@endsection

@section('page-title')
    {{ trans('general.menu.dashboard') }}
@endsection

@section('content')
    <div class="container">
        <div class="row">

            <div class="col-md-4">

                <div class="panel panel-default">
                    <div class="panel-heading">&nbsp;</div>
                    <div class="panel-body text-center">

                        <img class="profile-img" src="{{ $user->avatar }}" alt="{{ $user->name }}">

                        <h3>{{ $user->name }}</h3>
                        @isset($user->title)
                            <h5>{{ $user->title }}</h5>
                        @endisset
                        <div class="profile-links">
                            @isset($user->linkedin_link)
                                <a href="{{ $user->linkedin_link }}" target="_blank">
                                    <i class="fa fa-linkedin"></i>
                                </a>
                            @endisset
                            @isset($user->facebook_link)
                                <a href="{{ $user->facebook_link }}" target="_blank">
                                    <i class="fa fa-facebook"></i>
                                </a>
                            @endisset
                            @isset($user->instagram_link)
                                <a href="{{ $user->instagram_link }}" target="_blank">
                                    <i class="fa fa-instagram"></i>
                                </a>
                            @endisset
                            @isset($user->twitter_link)
                                <a href="{{ $user->twitter_link }}" target="_blank">
                                    <i class="fa fa-twitter"></i>
                                </a>
                            @endisset
                            @isset($user->website_link)
                                <a href="{{ $user->website_link }}" target="_blank">
                                    <i class="fa fa-link"></i>
                                </a>
                            @endisset
                        </div>
                        <p>
                            <a href="{{ route('edit.profile', $user->id) }}">{{ trans('general.home.edit_profile') }}</a>
                        </p>
                        @if (!($follows = $user->follows()->limit(3)->get())->isEmpty())
                            <p class="text-left" style="max-width: 200px; margin: 0 auto">
                                <strong>{{ trans('general.home.follows') }}:</strong> <br />
                                @foreach ($follows as $orgs)
                                    {{ $orgs->name }}<br>
                                @endforeach
                            </p>
                        @endif
                    </div>
                </div>
                @if (count($subscriptions) > 0)
                    <div class="panel panel-default">
                        <div class="panel-heading">{{ trans('general.home.my-subscriptions') }}</div>
                        <div class="panel-body">
                            @foreach ($subscriptions as $subscription)
                                <div class="subscription-item">
                                    <a href="{{ route('organization.show', $subscription->organization) }}" class="text">
                                        {{ $subscription->organization->name }}
                                    </a>
                                    <button onclick="unsubscribe({{ $subscription->organization->id }}, '{{ $subscription->organization->name }}')" class="btn btn-primary" style="float: right">{{ trans('event.unsubscribe') }}</button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($user->organization_enabled)
                    <div class="panel panel-default">
                        <div class="panel-heading" style="display: flex; justify-content: space-between">
                            <span>{{ trans('general.home.my-referral-revenue') }}</span>
                            <span>{{ $total_revenue_amount }}</span>
                        </div>
                        <div class="panel-body">
                            @foreach ($referral_revenue_array as $key => $referral_revenue)
                                <div class="referral-monthly-item">
                                    <span>{{ $key }}</span>
                                    <span>{{ $referral_revenue }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-8">

                @if ($user->organization_enabled || $user->isAdmin())
                    @include('partials.manage_organizations')
                @endif

                @include('partials.follows_organizations')

                @if ($user->organization_enabled || $user->isAdmin())
                    @include('partials.manage_streams')
                @endif

                @if ($user->organization_enabled || $user->isAdmin())
                    @include('partials.manage_meetings')
                @endif

                @include('partials.my_upcoming_events')

                @include('partials.my_past_events')

                @if ($user->organization_enabled)
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            {{ trans('general.home.database') }} ({{ $user->getCustomersCount() }})
                        </div>
                        <div class="panel-body">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    {{ trans('event.notification') }}
                                </div>
                                <div class="panel-body">

                                    <form enctype="multipart/form-data">

                                        {{ csrf_field() }}

                                        <div class="row ">
                                            <div class="col-md-3">
                                                {{ trans('general.home.notification-title') }}
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                                    <input name="title" id="title" class="form-control">
                                                    @if ($errors->has('title'))
                                                        <div class="text-danger">{{ $errors->first('title') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row ">
                                            <div class="col-md-3">
                                                {{ trans('general.home.notification-message') }}
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                                    <textarea name="message" id="message" class="form-control"></textarea>
                                                    @if ($errors->has('message'))
                                                        <div class="text-danger">{{ $errors->first('message') }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row ">
                                            <div class="col-md-3">
                                                {{ trans('event.attach-image') }}
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                                    <input type="file" name="attach_img" id="attach_img" class="form-control" accept="image/*">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row ">
                                            <div class="col-md-3">
                                                {{ trans('event.attach-file') }}
                                            </div>
                                            <div class="col-md-9">
                                                <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                                    <input type="file" name="attach_file" id="attach_file" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <br>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <button class="btn btn-primary" style="float:right">{{ trans('event.send') }}</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                            <hr>
                            <table class="table table-hover" id="customers_list" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>{{ trans('auth.name') }}</th>
                                        <th>{{ trans(('auth.email')) }}</th>
                                        {{-- <th>Phone Number</th> --}}
                                    </tr>
                                </thead>
                            </table>
                            <hr>
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    {{ trans('general.home.csv-upload') }}
                                </div>
                                <div class="panel-body">
                                    {{ trans('general.home.please-upload-csvfile-to-update-customers-list') }}
                                    <form enctype="multipart/form-data">
                                        <input type="file" name="attach_file" id="attach_file" class="form-control" style="margin-top: 20px">
                                        {{ csrf_field() }}
                                        <button class="btn btn-primary" style="margin-top:15px;">{{ trans('general.home.upload') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
@section('scripts2')
    @if ($user->organization_enabled)
        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js" defer></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
    @endif
    <script type="module">
    @if($user->organization_enabled)
    $(document).ready(function(){
        $('#customers_list').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('home.getCustomers')}}",
                type: 'GET'
            },
            pageLength: 5,
            searchDelay: 1500,
            "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            columns: [
                { data: 'name' },
                { data: 'email' },
                // { data: 'phone_number' }
            ],
            "columnDefs": [
                {
                    "targets": 0,
                    "orderable": false,
                }
            ],
            "language": {
                "sInfo": "Showing _START_ to _END_ of _TOTAL_ Customers",
                "sInfoEmpty": "Showing 0 to 0 of 0 Customers",
                "infoFiltered" : "",
                oPaginate: {
                    sNext: '<i class="fa fa-forward"></i>',
                    sPrevious: '<i class="fa fa-backward"></i>',
                    sFirst: '<i class="fa fa-step-backward"></i>',
                    sLast: '<i class="fa fa-step-forward"></i>'
                }
            }
        });
    });
    @endif

    window.unsubscribe = (organization_id, organization_name) => {
        swal({
            title: '{{ trans("organization.unsubscribe-from") }} ' + organization_name,
            text: "{{ trans('organization.are-you-sure-unsubscribe') }}?",
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            icon: "warning",
            buttons: true
        }).then((result) => {
            if (result) {
                let url = "{{ route('organization.unsubscribe', ':id') }}";
                url = url.replace(':id', organization_id);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                swal({title : "{{ trans('event.please-wait') }}", text : "{{ trans('organization.processing-unsubscribe-now') }}...", buttons: false, closeOnClickOutside: false, icon: "info"});
                $.ajax({
                    type:'POST',
                    url,
                    data:{
                        organization: organization_id
                    },
                    success:function(data){
                        if (data.success){
                            swal({text: "{{ trans('organization.you-unsubscribe-successfully') }}!", icon:"success", allowOutsideClick: false}).then(() =>{
                                window.location.reload();
                            })
                        }
                    },
                    error: function(){
                        window.location.reload();
                    }
                });
            }
        });
    }
</script>
@endsection
