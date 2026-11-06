@extends('layouts.app')

@section('meta')
<title>Event Feedback | {{ env('APP_NAME') }}</title>
@endsection

@section('page-title')
User Feedback for @ {{ $event->name }}
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">

                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12 event-title-info">
                            <h1 class="event-title">{{ $event->name }}</h1>
                            <table id="purchaser_list" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th width="30%">Name</th>
                                        <th width="30%">Rating</th>
                                        <th width="30%">Review</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($feedbacks as $row)
                                    <tr>
                                        <td>{{$row->user->name}}</td>
                                        <td>{{$row->rating}}</td>
                                        <td>{{$row->review}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $feedbacks->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
@endsection
