<div class="form-group{{ $errors->has('name') || $errors->has('slug') ? ' has-error' : '' }}">
    <label>{{ trans('organization.organization-name') }} <span class="text-danger">* {{ trans('event.required') }}</span></label>
    <input type="text" name="name" id="name" placeholder="{{ trans('organization.organization-name') }}" class="form-control" value="{{ old('name') ? old('name') : (isset($organization->name) ? $organization->name : '') }}" required>
    @if ($errors->has('name'))
        <p class="text-danger">{{ $errors->first('name') }}{{ $errors->first('slug') }}</p>
    @endif
</div>

<br>
<div class="form-group{{ $errors->has('autocomplete') ? ' has-error' : '' }}">
    <label>{{ trans('event.location') }} <span class="text-danger">* {{ trans('event.required') }}</span></label>
    <input type="text" name="autocomplete" id="autocomplete" placeholder="{{ trans('event.loading') }}..." class="form-control" value="{{ isset($organization) ? $organization->locationString() : '' }}" required>
    @if ($errors->has(['autocomplete']))
        <p class="text-danger">{{ trans('organization.location-field-is-required') }}</p>
    @endif
</div>

<br>
<div class="form-group{{ $errors->has('category') ? ' has-error' : '' }}">
    <label>{{ trans('organization.category') }} <span class="text-danger">* {{ trans('event.required') }}</span></label>
    <select name="category" class="form-control">
        <option {{ isset($organization) && $organization->category == 'Social' ? 'selected' : '' }}>
            {{ trans('organization.social') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'ESports' ? 'selected' : '' }}>
            {{ trans('organization.esports') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Athletic' ? 'selected' : '' }}>
            {{ trans('organization.athletic') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Political' ? 'selected' : '' }}>
            {{ trans('organization.political') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Corporate' ? 'selected' : '' }}>
            {{ trans('organization.corporate') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Dance' ? 'selected' : '' }}>
            {{ trans('organization.dance') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Faith-and-Worship' ? 'selected' : '' }} value="Faith-and-Worship">
            {{ trans('organization.faith-and-worship') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Thought-Leadership' ? 'selected' : '' }} value="Thought-Leadership">
            {{ trans('organization.thought-leadership') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Education' ? 'selected' : '' }}>
            {{ trans('organization.education') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Blockchain' ? 'selected' : '' }}>
            {{ trans('organization.blockchain') }}
        </option>
        <option {{ isset($organization) && $organization->category == 'Philanthropy-and-Humanitarian' ? 'selected' : '' }} value="Philanthropy-and-Humanitarian">
            {{ trans('organization.philanthropy-humanitarian') }}
        </option>
    </select>
    @if ($errors->has(['category']))
        <p class="text-danger">{{ $errors->first('category') }}</p>
    @endif
</div>

<br><br><br>
@isset($organization->cover_image)
    <div class="col-sm-12">
        <center>
            <img src="{{ $organization->cover_image ? $organization->cover_image : '' }}" alt="Organization Cover Photo" class="profile-img">
        </center>
    </div>
@endisset

<label>{{ trans('event.profile-image') }}</label>
{{ html()->file('cover_image')->class('form-control')->acceptImage() }}
@if ($errors->has('cover_image'))
    <p class="text-danger">{{ $errors->first('cover_image') }}</p>
@endif

<hr>

<div class="form-group{{ $errors->has('website_link') ? ' has-error' : '' }}">
    <label>{{ trans('organization.website-fulllink') }}</label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-link" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="website_link" value="{{ old('website_link') ? old('website_link') : (isset($organization) && isset($organization->website_link) ? $organization->website_link : '') }}" placeholder="{{ trans('organization.website-fulllink') }}">
    </div>
    @if ($errors->has('website_link'))
        <span class="help-block">
            <strong>{{ $errors->first('website_link') }}</strong>
        </span>
    @endif
</div>

<div class="form-group{{ $errors->has('facebook_link') ? ' has-error' : '' }}">
    <label>{{ trans('organization.facebook-fulllink') }}</label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-facebook-official" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="facebook_link" value="{{ old('facebook_link') ? old('facebook_link') : (isset($organization) && isset($organization->facebook_link) ? $organization->facebook_link : '') }}" placeholder="{{ trans('organization.facebook-fulllink') }}">
    </div>
    @if ($errors->has('facebook_link'))
        <span class="help-block">
            <strong>{{ $errors->first('facebook_link') }}</strong>
        </span>
    @endif
</div>

<div class="form-group{{ $errors->has('linkedin_link') ? ' has-error' : '' }}">
    <label>{{ trans('organization.linkedin-fulllink') }}</label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-linkedin-square" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="linkedin_link" value="{{ old('linkedin_link') ? old('linkedin_link') : (isset($organization) && isset($organization->linkedin_link) ? $organization->linkedin_link : '') }}" placeholder="{{ trans('organization.linkedin-fulllink') }}">
    </div>
    @if ($errors->has('linkedin_link'))
        <span class="help-block">
            <strong>{{ $errors->first('linkedin_link') }}</strong>
        </span>
    @endif
</div>
<div class="form-group{{ $errors->has('instagram_link') ? ' has-error' : '' }}">
    <label>{{ trans('organization.instagram-fulllink') }}</label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-instagram" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="instagram_link" value="{{ old('instagram_link') ? old('instagram_link') : (isset($organization) && isset($organization->instagram_link) ? $organization->instagram_link : '') }}" placeholder="{{ trans('organization.instagram-fulllink') }}">
    </div>
    @if ($errors->has('instagram_link'))
        <span class="help-block">
            <strong>{{ $errors->first('instagram_link') }}</strong>
        </span>
    @endif
</div>
<div class="form-group{{ $errors->has('twitter_link') ? ' has-error' : '' }}">
    <label>{{ trans('organization.twitter-fulllink') }}</label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-twitter-square" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="twitter_link" value="{{ old('twitter_link') ? old('twitter_link') : (isset($organization) && isset($organization->twitter_link) ? $organization->twitter_link : '') }}" placeholder="{{ trans('organization.twitter-fulllink') }}">
    </div>
    @if ($errors->has('twitter_link'))
        <span class="help-block">
            <strong>{{ $errors->first('twitter_link') }}</strong>
        </span>
    @endif
</div>

<div class="form-group{{ $errors->has('mail_link') ? ' has-error' : '' }}">
    <label>{{ trans('organization.email-link') }}</label>
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-envelope" aria-hidden="true"></i></span>
        <input type="text" class="form-control" name="mail_link" value="{{ old('mail_link') ? old('mail_link') : (isset($organization) && isset($organization->mail_link) ? $organization->mail_link : '') }}" placeholder="{{ trans('organization.email-link') }}">
    </div>
    @if ($errors->has('mail_link'))
        <span class="help-block">
            <strong>{{ $errors->first('mail_link') }}</strong>
        </span>
    @endif
</div>
<hr>

<div class="form-group{{ $errors->has('currency') ? ' has-error' : '' }}">
    <?php
    $currencies = \App\Models\Helpers::$currencies;
    ?>
    <label>{{ trans('organization.currency') }} <span class="text-danger">* {{ trans('event.required') }}</span></label>
    <select name="currency" class="form-control">
        @foreach ($currencies as $item)
            <option {{ isset($organization) && $organization->currency == $item['code'] ? 'selected' : '' }}>{{ $item['code'] }}</option>
        @endforeach
    </select>
    @if ($errors->has(['currency']))
        <p class="text-danger">{{ $errors->first('currency') }}</p>
    @endif
</div>

<bank-information-form
    @if (isset($organization->bank_address)) :initial_bank_address= "'{{ $organization->bank_address }}'" @endif
    @if (isset($organization->bank_info_first_name)) :initial_bank_info_first_name= "'{{ $organization->bank_info_first_name }}'" @endif
    @if (isset($organization->bank_info_last_name)) :initial_bank_info_last_name= "'{{ $organization->bank_info_last_name }}'" @endif
    @if (isset($organization->bank_info_personal_address)) :initial_bank_info_personal_address= "'{{ $organization->bank_info_personal_address }}'" @endif
    @if (isset($organization->bank_info_business_email)) :initial_bank_info_business_email= "'{{ $organization->bank_info_business_email }}'" @endif
    @if (isset($organization->bank_info_swift_code)) :initial_bank_info_swift_code= "'{{ $organization->bank_info_swift_code }}'" @endif
    @if (isset($organization->bank_routing_number)) :initial_bank_routing_number= "'{{ $organization->bank_routing_number }}'" @endif
    @if (isset($organization->bank_account_number)) :initial_bank_account_number="'{{ $organization->bank_account_number }}'" @endif
    :org_create="'{{ isset($orgCreate) ? $orgCreate : 0 }}'"
    @if (isset($organization->is_subscription)) :is_subscription= "'{{ $organization->is_subscription }}'"
    :monthly_subscription_amount="'{{ $organization->monthly_subscription_amount }}'"
    :yearly_subscription_amount="'{{ $organization->yearly_subscription_amount }}'" @endif
    @if (isset($organization->is_crypto)) :is_crypto= "'{{ $organization->is_crypto }}'"
    :wallet_address="'{{ $organization->wallet_address }}'" @endif
    @if (isset($organization->promo_codes)) :promo_codes="{{ json_encode($organization->promo_codes) }}" @endif
    :i18n="{{ json_encode([
        'bank_information_for_payout' => trans('organization.bank-information-for-payout'),
        'first_name' => trans('checkout.first-name'),
        'last_name' => trans('checkout.last-name'),
        'personal_address' => trans('organization.personal-address'),
        'bank_address' => trans('organization.bank-address'),
        'business_email' => trans('organization.business-email'),
        'swift_code' => trans('organization.swift-code'),
        'bank_routing_number' => trans('organization.bank-routing-number'),
        'bank_account_number' => trans('organization.bank-account-number'),
        'enable_subscription' => trans('organization.enable-subscription'),
        'monthly_amount' => trans('organization.monthly-amount'),
        'annual_amount' => trans('organization.annual-amount'),
        'download_your_list_before_editing' => trans('organization.download-your-list-before-editing'),
        'once_edit_your_subscribers_list_not_accessable' => trans('organization.once-edit-your-subscribers-list-not-accessable'),
        'enable_crypto_payment' => trans('organization.enable-crypto-payment'),
        'wallet_address_etherum' => trans('organization.wallet-address-etherum'),
        'promo_code' => trans('checkout.promocode'),
        'generate_new' => trans('organization.generate-new'),
        'email' => trans('auth.email'),
        'percent' => trans('organization.percent'),
        'discount_percent' => trans('organization.discount-percent'),
        'send_again' => trans('organization.send-again'),
        'affiliate' => trans('organization.affiliate'),
        'this_promo_code_in_use' => trans('organization.this-promo-already-in-use'),
        'create_organization' => trans('organization.create-organization'),
        'update_organization' => trans('organization.update-organization')
    ]) }}"
    >
</bank-information-form>

@section('scripts')
    <script>
        function initAutocomplete() {
            var componentForm = {
                street_number: 'short_name',
                route: 'long_name',
                locality: 'long_name',
                administrative_area_level_1: 'short_name',
                country: 'long_name',
                postal_code: 'short_name'
            };

            var input = document.getElementById('autocomplete');
            input.setAttribute('placeholder', '{{ trans("event.enter-street-address") }} (e.g. 2401 Tampa Dr, Orlando Fl 34219)');

            var autocomplete = new google.maps.places.Autocomplete(input, {
                types: ['geocode']
            });
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_FRONT_KEY') }}&libraries=places&callback=initAutocomplete" async defer></script>
    <script type="module">
    $('form').submit(() => {
            $("#submit-btn").attr("disabled", true);
            let website_link = $('[name="website_link"]').val() ? $('[name="website_link"]').val().toLowerCase() : null;
            let facebook_link = $('[name="facebook_link"]').val() ? $('[name="facebook_link"]').val().toLowerCase() : null;
            let instagram_link = $('[name="instagram_link"]').val() ? $('[name="instagram_link"]').val().toLowerCase() : null;
            let linkedin_link = $('[name="linkedin_link"]').val() ? $('[name="linkedin_link"]').val().toLowerCase() : null;
            let twitter_link = $('[name="twitter_link"]').val() ? $('[name="twitter_link"]').val().toLowerCase() : null;

            if (website_link && !website_link.startsWith("https://") && !website_link.startsWith("http://")) {
                website_link = "https://" + website_link;
                $('[name="website_link"]').val(website_link);
            }

            if (facebook_link && !facebook_link.startsWith("https://") && !facebook_link.startsWith("http://")) {
                facebook_link = "https://" + facebook_link;
                $('[name="facebook_link"]').val(facebook_link);
            }

            if (instagram_link && !instagram_link.startsWith("https://") && !instagram_link.startsWith("http://")) {
                instagram_link = "https://" + instagram_link;
                $('[name="instagram_link"]').val(instagram_link);
            }

            if (linkedin_link && !linkedin_link.startsWith("https://") && !linkedin_link.startsWith("http://")) {
                linkedin_link = "https://" + linkedin_link;
                $('[name="linkedin_link"]').val(linkedin_link);
            }

            if (twitter_link && !twitter_link.startsWith("https://") && !twitter_link.startsWith("http://")) {
                twitter_link = "https://" + twitter_link;
                $('[name="twitter_link"]').val(twitter_link);
            }

        })
</script>
@endsection
