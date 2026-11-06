@extends('layouts.app')

@section('content')
    <div class="container">

        <h1 class="title">
            Admin Dashboard
            <hr>
        </h1>

        <div class="col-md-6 text-center">
            <div class="panel panel-default">
                <div class="panel-body">
                  <h4>Sales</h4>
                    This Week: ${{ $salesThis }} / Last Week: ${{ $salesLast }}
                </div>
            </div>
        </div>

        <div class="col-md-6 text-center">
            <div class="panel panel-default">
                <div class="panel-body">
                  <h4>Refunds</h4>
                    This Week: ${{ $refundsThis }} / Last Week: ${{ $refundsLast }}
                </div>
            </div>
        </div>

        <br><br>

        <div class="col-md-3 text-center">
            <div class="panel panel-default">
                <div class="panel-body">
                  <h4>Users created</h4>
                    This Week: {{ $usersThis->total() }} / Last Week: {{ $usersLast->total() }}
                    <br><br>
                    <a style="font-size: 13px !important;" href="/admin/users" class="btn btn-primary o">View All Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 text-center">
            <div class="panel panel-default">
                <div class="panel-body">
                  <h4>Organizations created</h4>
                    This Week: {{ $organizationsThis->total() }} / Last Week: {{ $organizationsLast->total() }}
                    <br><br>
                    <a style="font-size: 13px !important;" href="/admin/organization" class="btn btn-primary o">View All Organizations</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 text-center">
            <div class="panel panel-default">
                <div class="panel-body">
                  <h4>Events created</h4>
                    This Week: {{ $eventsThis->total() }} / Last Week: {{ $eventsLast->total() }}
                    <br><br>
                    <a style="font-size: 13px !important;" href="/admin/events" class="btn btn-primary o">View All Events</a>
                </div>
            </div>
        </div>
        <div class="col-md-3 text-center">
            <div class="panel panel-default">
                <div class="panel-body">
                    <h4>Ad Banners</h4>
                    Click Counts: {{$banner_click}}
                    <br><br>
                    <a style="font-size: 13px !important;" href="/admin/banner" class="btn btn-primary o">View Ad Banners</a>

                </div>
            </div>
        </div>

    </div>
@endsection
