<div class="panel panel-default">
    <div class="panel-heading">
        {{ $title }}
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-12">
                <span class="text-muted">
                    {{ $slot }}
                </span>
            </div>
        </div>

        <div class="row">

            <div class="col-md-10">
                <select name="theatre" class="form-control">
                    @foreach($theatres as $theatre)
                        <option {{ isset($theatre_id) && $theatre_id === $theatre->id ? 'selected' : '' }} value={{ $theatre->id }}>{{ $theatre->name }} - {{ $theatre->location }}</option>
                    @endforeach
                    <option value="" {{ !isset($theatre_id) ? 'selected' : '' }}>{{ trans('event.select-theatre') }}</option>
                </select>
            </div>
            <div class="col-md-12" style="text-align: right">
                {{ trans('event.please-contact') }} <a href="mailto:mvp@eventssmarter.com">mvp@eventssmarter.com</a> {{ trans('event.to-add-more-venues') }}
            </div>
        </div>
    </div>

</div>
