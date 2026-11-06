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
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <img class="event-cover w-full" src="{{ $event->image }}" alt="{{ $event->name }}">
                        </div>
                        <div class="col-sm-8 event-title-info">
                            <h1 class="event-title">{{ $event->name }}</h1>
                            <p class="event-desc">{{ $event->description }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="times text-center">
                                @if($event->starts_at)
                                <div class="col-md-5">
                                    <h4>
                                        {{ $event->starts_at->format('l M j') }}<br>
                                        {{ $event->starts_at->format('g:ia') }}<br>
                                    </h4>
                                </div>
                                <div class="col-md-1">
                                    to
                                </div>
                                <div class="col-md-5">
                                    <h4>
                                        {{ $event->ends_at->format('l M j') }}<br>
                                        {{ $event->ends_at->format('g:ia') }}<br>
                                    </h4>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                @if(!$feedback->count())
                <div class="panel-heading">
                    Leave Your Feedback
                </div>
                <div class="panel-body">
                    {{ html()->form('POST',route('feedback.update'))->open() }}
                    {{ csrf_field() }}
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    <div class="row ">
                        <div class="col-md-3">
                            Rating
                        </div>
                        <div class="col-md-9">
                            <div class="form-group {{ $errors->has('message') ? 'has-error' : '' }}">
                                <label class="radio-inline"><input type="radio" value=1 name="rating">1</label>
                                <label class="radio-inline"><input type="radio" value=2 name="rating">2</label>
                                <label class="radio-inline"><input type="radio" value=3 name="rating">3</label>
                                <label class="radio-inline"><input type="radio" value=4 name="rating">4</label>
                                <label class="radio-inline"><input type="radio" value=5 name="rating">5</label>
                                @if($errors->has('rating'))
                                <div class="text-danger">{{ $errors->first('rating') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row ">
                        <div class="col-md-3">
                            Review
                        </div>
                        <div class="col-md-9">
                            <div class="form-group {{ $errors->has('review') ? 'has-error' : '' }}">
                                <textarea name="review" id="review" class="form-control"></textarea>
                                @if($errors->has('review'))
                                <div class="text-danger">{{ $errors->first('review') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-11">
                            <button class="btn btn-primary" id="pay-btn">Save</button>
                        </div>
                    </div>
                    {{ html()->form()->close() }}
                </div>
                @else
                <div class="panel-heading">
                    You have already left feedback for this event.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js" defer></script>
@endsection
