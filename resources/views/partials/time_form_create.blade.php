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
                @if($errors->has($field_name.'.*'))
                    <br>
                    <span class="text-danger">{{ $errors->first($field_name.'.*') }}</span>
                @elseif($errors->has($field_name))
                    <br>
                    <span class="text-danger">{{ $errors->first($field_name) }}</span>
                @else
                    <br><br>
                @endif
            </div>
            <div class="col-md-3">
                {{ trans('event.date') }}:
            </div>

            <div class="col-md-6">
                <input type="date" class="form-control" name="{{ $field_name }}[date]" placeholder="mm/dd/yyyy" value="{{ old($field_name.'.date') }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                {{ trans('event.time') }}:
            </div>

            <div class="col-md-6 col-xs-4">
                <select name="{{ $field_name }}[time]" class="form-control" required>
                    <option {{ old($field_name.'.time')=='1:00'?'selected':'' }}>1:00</option>
                    <option {{ old($field_name.'.time')=='1:15'?'selected':'' }}>1:15</option>
                    <option {{ old($field_name.'.time')=='1:30'?'selected':'' }}>1:30</option>
                    <option {{ old($field_name.'.time')=='1:45'?'selected':'' }}>1:45</option>
                    <option {{ old($field_name.'.time')=='2:00'?'selected':'' }}>2:00</option>
                    <option {{ old($field_name.'.time')=='2:15'?'selected':'' }}>2:15</option>
                    <option {{ old($field_name.'.time')=='2:30'?'selected':'' }}>2:30</option>
                    <option {{ old($field_name.'.time')=='2:45'?'selected':'' }}>2:45</option>
                    <option {{ old($field_name.'.time')=='3:00'?'selected':'' }}>3:00</option>
                    <option {{ old($field_name.'.time')=='3:15'?'selected':'' }}>3:15</option>
                    <option {{ old($field_name.'.time')=='3:30'?'selected':'' }}>3:30</option>
                    <option {{ old($field_name.'.time')=='3:45'?'selected':'' }}>3:45</option>
                    <option {{ old($field_name.'.time')=='4:00'?'selected':'' }}>4:00</option>
                    <option {{ old($field_name.'.time')=='4:15'?'selected':'' }}>4:15</option>
                    <option {{ old($field_name.'.time')=='4:30'?'selected':'' }}>4:30</option>
                    <option {{ old($field_name.'.time')=='4:45'?'selected':'' }}>4:45</option>
                    <option {{ old($field_name.'.time')=='5:00'?'selected':'' }}>5:00</option>
                    <option {{ old($field_name.'.time')=='5:15'?'selected':'' }}>5:15</option>
                    <option {{ old($field_name.'.time')=='5:30'?'selected':'' }}>5:30</option>
                    <option {{ old($field_name.'.time')=='5:45'?'selected':'' }}>5:45</option>
                    <option value="" selected disabled hidden>{{ trans('event.select-time') }}</option>
                    <option {{ old($field_name.'.time')=='6:00'?'selected':'' }}>6:00</option>
                    <option {{ old($field_name.'.time')=='6:15'?'selected':'' }}>6:15</option>
                    <option {{ old($field_name.'.time')=='6:30'?'selected':'' }}>6:30</option>
                    <option {{ old($field_name.'.time')=='6:45'?'selected':'' }}>6:45</option>
                    <option {{ old($field_name.'.time')=='7:00'?'selected':'' }}>7:00</option>
                    <option {{ old($field_name.'.time')=='7:15'?'selected':'' }}>7:15</option>
                    <option {{ old($field_name.'.time')=='7:30'?'selected':'' }}>7:30</option>
                    <option {{ old($field_name.'.time')=='7:45'?'selected':'' }}>7:45</option>
                    <option {{ old($field_name.'.time')=='8:00'?'selected':'' }}>8:00</option>
                    <option {{ old($field_name.'.time')=='8:15'?'selected':'' }}>8:15</option>
                    <option {{ old($field_name.'.time')=='8:30'?'selected':'' }}>8:30</option>
                    <option {{ old($field_name.'.time')=='8:45'?'selected':'' }}>8:45</option>
                    <option {{ old($field_name.'.time')=='9:00'?'selected':'' }}>9:00</option>
                    <option {{ old($field_name.'.time')=='9:15'?'selected':'' }}>9:15</option>
                    <option {{ old($field_name.'.time')=='9:30'?'selected':'' }}>9:30</option>
                    <option {{ old($field_name.'.time')=='9:45'?'selected':'' }}>9:45</option>
                    <option {{ old($field_name.'.time')=='10:00'?'selected':'' }}>10:00</option>
                    <option {{ old($field_name.'.time')=='10:15'?'selected':'' }}>10:15</option>
                    <option {{ old($field_name.'.time')=='10:30'?'selected':'' }}>10:30</option>
                    <option {{ old($field_name.'.time')=='10:45'?'selected':'' }}>10:45</option>
                    <option {{ old($field_name.'.time')=='11:00'?'selected':'' }}>11:00</option>
                    <option {{ old($field_name.'.time')=='11:15'?'selected':'' }}>11:15</option>
                    <option {{ old($field_name.'.time')=='11:30'?'selected':'' }}>11:30</option>
                    <option {{ old($field_name.'.time')=='11:45'?'selected':'' }}>11:45</option>
                    <option {{ old($field_name.'.time')=='12:00'?'selected':'' }}>12:00</option>
                    <option {{ old($field_name.'.time')=='12:15'?'selected':'' }}>12:15</option>
                    <option {{ old($field_name.'.time')=='12:30'?'selected':'' }}>12:30</option>
                    <option {{ old($field_name.'.time')=='12:45'?'selected':'' }}>12:45</option>
                </select>
            </div>

            <div class="col-md-2 col-xs-4">
                <select name="{{ $field_name }}[am]" class="form-control">
                    <option>AM</option>
                    <option selected>PM</option>
                </select>
            </div>
        </div>
    </div>

</div>
