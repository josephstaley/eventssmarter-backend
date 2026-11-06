@extends('layouts.app')

@section('content')
    <div class="container">

        <h4><a href="/admin">Admin</a> / {{ str_plural($name) }}</h4>

        <br>

        <p>Showing {{ $model->count() .' of '. $model->total() }}</p>

        <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th>Name</th>
                @if($name == 'Event')
                <th>Organization</th>
                @endif
                @if($name == 'Organization')
                <th style="text-align:center">Application Fee Percentage</th>
                <th style="text-align:center">Sales Delay Time</th>
                @endif
                <th style="text-align:center">Created</th>
                @if($name == 'Event')
                <th>Export</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($model as $row)
                <tr>
                    <td><a href="/admin/{{ str_slug($name) }}/edit/{{ $row->id }}">Edit</a></td>
                    @if($name == 'Event' && isset($row->organization))
                    <td>
                        <a href="{{ $row->link('events.show') }}" target="_blank">
                            {{ $row->name }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('organization.show', $row->organization) }}" target="_blank">
                            {{ $row->organization->name }}
                        </a>
                    </td>
                    @elseif($name == 'Organization')
                    <td>
                        <a href="{{ route('organization.show', $row) }}" target="_blank">
                            {{ $row->name }}
                        </a>
                    </td>
                    @else
                    <td>{{ $row->name }}</td>
                    <td>Organization deleted</td>
                    @endif
                    @if($name == 'Organization')
                    <td style="text-align:center">{{ $row->app_fee_percentage }}</td>
                    <td style="text-align:center">{{ $row->sales_time_delay }}</td>
                    @endif
                    <td style="text-align:center">{{ $row->created_at }}</td>
                    @if($name == 'Event')
                    <td>
                        <a class="btn btn-primary btn-xs" style="margin-left:4px;" href="{{ route('event.adminExportForStripe', $row->id) }}" target="_blank">
                            <i class="fa fa-download"></i>
                        </a>
                    </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $model->render() }}

        @if($model->isEmpty())
            No Results
        @endempty

    </div>
@endsection