<div class="form-group{{ $errors->has('name') || $errors->has('slug') ? ' has-error' : '' }}">
    <label>Banner Name <span class="text-danger">* Required</span></label>
    <input type="text" name="name" id="name" placeholder="Banner Name" class="form-control" value="{{ old('name') ? old('name') : (isset($banner->name) ? $banner->name : '') }}">

</div>

<br>
@isset($banner->image)
<div class="col-sm-12">
    <center>
        <img src="{{ $banner->image ? $banner->image : '' }}" alt="Banner image" class="profile-img">
    </center>
</div>
@endisset

<label>Banner Image</label>
{{ html()->file('image')->class('form-control')->acceptImage() }}
@if($errors->has('image'))
    <p class="text-danger">{{ $errors->first('image') }}</p>
@endif

<hr>

<div class="form-group{{ $errors->has('link_url') ? ' has-error' : '' }}">
    <label>Link URL <span class="text-danger">* Required</span> </label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-link" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="link_url" value="{{ old('link_url') ? old('website_link') : (isset($banner->link_url) ? $banner->link_url : '') }}" placeholder="Link URL">
    </div>
    @if ($errors->has('link_url'))
        <span class="help-block">
            <strong>{{ $errors->first('link_url') }}</strong>
        </span>
    @endif
</div>
<br>


<div class="form-group" style="display: flex">
    <div>
        <label>Link Type</label>
        <select style="margin-left: 10px" id="link_type" name="link_type">
            <option value="0" id="event" selected>Event</option>
            <option value="1" id="org">Organization</option>
        </select>
    </div>
    <div class="col-md-offset-2" >
        <label>Link to</label>
        <select style="margin-left: 10px;width: 200px" id="link_id" name="link_id">
            <?php
            foreach($events as $event) {
            ?> <option class="event" value="<?php echo $event->id?>"> <?php echo $event->name ?></option><?php
            }
            ?>
            <?php
            foreach($organizations as $organization) {
            ?> <option class="organization" hidden value="<?php echo $organization->id?>"> <?php echo $organization->name ?></option><?php
            }
            ?>

        </select>
    </div>
</div>
