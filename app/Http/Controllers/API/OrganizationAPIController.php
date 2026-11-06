<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Event;
use App\Helper\Helpers as Helper;
use App\Models\PromoCode;
use App\Models\Banner;
use App\Models\Subscription;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PromoRegister;

class OrganizationAPIController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::orderBy('created_at', 'desc')->with(['events' => fn($query) => $query->where('events.ends_at', '>=', Carbon::now())->where('events.is_private', 0)]);
        if ($request->category) {
            $query = $query->where('category', $request->category);
        }
        $organizations = $query->paginate(12);
        $organizations->getCollection()->transform(function ($item) {
            return $item->makeHidden([
                'bank_account_number',
                'bank_routing_number',
                'bank_address',
                'bank_info_business_email',
                'bank_info_first_name',
                'bank_info_last_name',
                'bank_info_personal_address',
                'bank_info_swift_code',
                'bank_routing_number',
                'paypal_merchant_id',
                'sales_time_delay',
                'zelle_email',
                'zelle_phone_number'
            ]);
        });

        return $this->sendResponse($organizations, 'success');
    }

    public function organizationDetail(Request $request)
    {
        $organization = Organization::where('slug', $request->org_slug)->first();
        if ($organization) {
            $organization->makeHidden(
                'bank_account_number',
                'bank_routing_number',
                'bank_address',
                'bank_info_business_email',
                'bank_info_first_name',
                'bank_info_last_name',
                'bank_info_personal_address',
                'bank_info_swift_code',
                'bank_routing_number',
                'paypal_merchant_id',
                'sales_time_delay',
                'zelle_email',
                'zelle_phone_number'
            );
            $events = Event::where('organization_id', $organization->id)->where('ends_at', '>=', Carbon::now())->where('events.is_private', 0)->with([
                'organization' => function ($query) {
                    $query->select('id', 'category', 'name', 'slug');
                }
            ])->orderBy('ends_at')->paginate(12);
            $banners = Banner::where('link_id', $organization->id)->where('link_type', '1')->get();
            return $this->sendResponse(['organization' => $organization, 'events' => $events, 'banners' => $banners], 'success');
        }

        return $this->sendError('No organization!');
    }

    public function myOrganizations()
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $organizations = $user->organizations()->orderBy('created_at', 'desc')->with(['events' => fn($query) => $query->where('events.ends_at', '>=', Carbon::now())])->get()->makeHidden(['paypal_merchant_id', 'pivot'])->makeVisible(['password']);
        return $this->sendResponse($organizations, 'success');
    }

    public function createOrganization(Request $request)
    {
        $this->validate($request, [
            'location' => 'required'
        ]);
        $address = app('geocoder')->geocode($request->location)->get()->first();
        $request->merge([
            'slug' => str_slug($request->name),
            'zip' => isset($address) ? $address->getPostalCode() : null,
            'state' => isset($address) && count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
            'city' => isset($address) ? $address->getLocality() : null,
            'street' => isset($address) ? $address->getStreetName() : null,
            'street_number' => isset($address) ? $address->getStreetNumber() : null,
            'lat' => isset($address) ? $address->getCoordinates()->getLatitude() : null,
            'lng' => isset($address) ? $address->getCoordinates()->getLongitude() : null,
            'address' => $request->location,
        ]);
        $fields = $this->validate($request, [
            'name' => 'required|unique:organizations',
            'slug' => 'required|unique:organizations',
            'category' => 'required|min:3',
            'cover_image' => 'nullable',
            'facebook_link' => 'nullable',
            'linkedin_link' => 'nullable',
            'twitter_link' => 'nullable',
            'website_link' => 'nullable',
            'instagram_link' => 'nullable',
            'mail_link' => 'nullable|email',
            'zip' => '',
            'state' => '',
            'city' => '',
            'street' => '',
            'street_number' => '',
            'lat' => '',
            'lng' => '',
            'address' => '',
            'currency' => 'required',
            'is_subscription' => 'required',
            'yearly_subscription_amount' => 'nullable',
            'monthly_subscription_amount' => 'nullable',
            'is_crypto' => 'required',
            'wallet_address' => 'nullable',
            'bank_info_first_name' => 'nullable',
            'bank_info_last_name' => 'nullable',
            'bank_info_business_email' => 'nullable',
            'bank_routing_number' => 'nullable',
            'bank_account_number' => 'nullable',
            'bank_address' => 'nullable',
            'bank_info_personal_address' => 'nullable',
            'bank_info_swift_code' => 'nullable',
            'zelle_email' => 'nullable',
            'zelle_phone_number' => 'nullable',
        ], [
            'name.unique' => 'Name has already been taken. Please use another one!',
            'slug.unique' => 'Name has already been taken. Please use another one!'
        ]);

        //subscription validate
        if (isset($fields['is_subscription']) && $fields['is_subscription']) {
            $this->validate($request, [
                'yearly_subscription_amount' => 'required',
                'monthly_subscription_amount' => 'required'
            ]);
            // create subscription
            try {
                $name = $fields['name'];
                $fields['is_subscription'] = 1;
                $fields['subscription_product_id'] = Helper::createProductOnPaypal($name . "'s Subscription Product", $fields['slug']);
                $fields['monthly_subscription_price_id'] = $fields['monthly_subscription_amount'] > 0 ? Helper::createMonthlyPlanOnPaypal(
                    $fields['subscription_product_id'],
                    $name . ' Monthly Subscription',
                    'Monthly Subscription (1 Month Trial)',
                    $fields['monthly_subscription_amount'],
                    $fields['currency']
                ) : 'free-plan';
                $fields['yearly_subscription_price_id'] = $fields['yearly_subscription_amount'] > 0 ? Helper::createYearlyPlanOnPaypal(
                    $fields['subscription_product_id'],
                    $name . ' Yearly Subscription',
                    'Yearly Subscription (1 Month Trial)',
                    $fields['yearly_subscription_amount'],
                    $fields['currency']
                ) : 'free-plan';
            } catch (\Throwable $th) {
                return $this->sendError($th->getMessage(), 422);
            }
        }
        if (!isset($fields['cover_image'])) {
            $fields['cover_image'] = 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/05_Shop-128.png';
        }
        srand(time());
        $fields['password'] = rand(10000000, 99999999);
        $organization = $request->user()->organizations()->create($fields);

        //promo code
        $promo_code = $request->promo_code;
        $affiliate_email = $request->affiliate_email;
        $affiliate_percent = $request->affiliate_percent;
        $discount_percent = $request->discount_percent;
        if ($promo_code) {
            foreach ($promo_code as $key => $code) {
                $promo = PromoCode::where('organization_id', $organization->id)->where('promo_code', $code)->get()->first();
                if (!$promo) {
                    $promo = PromoCode::create([
                        'organization_id' => $organization->id,
                        'promo_code' => $promo_code[$key],
                        'affiliate_email' => $affiliate_email[$key],
                        'affiliate_percent' => $affiliate_percent[$key],
                        'discount_percent' => $discount_percent[$key],
                    ]);
                    Notification::route('mail', $affiliate_email[$key])->notify(new PromoRegister($organization, $code));
                }
            }
        }
        return $this->sendResponse($organization, 'success');
    }

    public function updateOrganization(Request $request, Organization $organization)
    {
        if ($request->location) {
            $address = app('geocoder')->geocode($request->location)->get()->first();
            $request->merge([
                'zip' => isset($address) ? $address->getPostalCode() : null,
                'state' => isset($address) && count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
                'city' => isset($address) ? $address->getLocality() : null,
                'street' => isset($address) ? $address->getStreetName() : null,
                'street_number' => isset($address) ? $address->getStreetNumber() : null,
                'lat' => isset($address) ? $address->getCoordinates()->getLatitude() : null,
                'lng' => isset($address) ? $address->getCoordinates()->getLongitude() : null,
                'address' => $request->location,
            ]);
        }
        $fields = $this->validate($request, [
            'category' => 'nullable|min:3',
            'cover_image' => 'nullable',
            'facebook_link' => 'nullable',
            'linkedin_link' => 'nullable',
            'twitter_link' => 'nullable',
            'website_link' => 'nullable',
            'instagram_link' => 'nullable',
            'mail_link' => 'nullable|email',
            'zip' => '',
            'state' => '',
            'city' => '',
            'street' => '',
            'street_number' => '',
            'lat' => '',
            'lng' => '',
            'address' => '',
            'currency' => 'nullable',
            'is_subscription' => 'required',
            'yearly_subscription_amount' => 'nullable',
            'monthly_subscription_amount' => 'nullable',
            'is_crypto' => 'nullable',
            'wallet_address' => 'nullable',
            'bank_info_first_name' => 'nullable',
            'bank_info_last_name' => 'nullable',
            'bank_info_business_email' => 'nullable',
            'bank_routing_number' => 'nullable',
            'bank_account_number' => 'nullable',
            'bank_address' => 'nullable',
            'bank_info_personal_address' => 'nullable',
            'bank_info_swift_code' => 'nullable',
            'zelle_email' => 'nullable',
            'zelle_phone_number' => 'nullable',
        ]);
        /** @var \App\Models\User */
        $user = auth()->user();
        $organization = $user->organizations()->where('slug', $organization->slug)->firstOrFail();
        if ($request->name && $request->name != $organization->name) {
            $request->merge([
                'slug' => str_slug($request->name)
            ]);
            $this->validate($request, [
                'name' => 'required|min:3|unique:organizations',
                'slug' => 'required|min:3|unique:organizations'
            ], [
                'name.unique' => 'Name has already been taken. Please use another one!',
                'slug.unique' => 'Name has already been taken. Please use another one!'
            ]);
            $fields['name'] = $request['name'];
            $fields['slug'] = $request['slug'];
        }
        //subscription
        if (isset($fields['is_subscription']) && $fields['is_subscription']) {
            $this->validate($request, [
                'yearly_subscription_amount' => 'required',
                'monthly_subscription_amount' => 'required'
            ]);
        }
        if (
            $request['is_subscription'] == $organization->is_subscription && $organization->is_subscription &&
            ($fields['yearly_subscription_amount'] != $organization->yearly_subscription_amount || $fields['monthly_subscription_amount'] != $organization->monthly_subscription_amount)
        ) {
            /* created subscription already but change the prices */
            // active false to old plan and create new plan
            $name = isset($fields['name']) ? $fields['name'] : $organization->name;
            if ($fields['yearly_subscription_amount'] != $organization->yearly_subscription_amount) {
                Helper::deactivatePlanOnPaypal($organization->yearly_subscription_price_id);
                $fields['yearly_subscription_price_id'] = $fields['yearly_subscription_amount'] > 0 ? Helper::createYearlyPlanOnPaypal(
                    $organization->subscription_product_id,
                    $name . ' Yearly Subscription',
                    'Yearly Subscription (1 Month Trial)',
                    $fields['yearly_subscription_amount'],
                    $fields['currency']
                ) : 'free-plan';
            }
            if ($fields['monthly_subscription_amount'] != $organization->monthly_subscription_amount) {
                Helper::deactivatePlanOnPaypal($organization->monthly_subscription_price_id);
                $fields['monthly_subscription_price_id'] = $fields['monthly_subscription_amount'] > 0 ? Helper::createMonthlyPlanOnPaypal(
                    $organization->subscription_product_id,
                    $name . ' Monthly Subscription',
                    'Monthly Subscription (1 Month Trial)',
                    $fields['monthly_subscription_amount'],
                    $fields['currency']
                ) : 'free-plan';
            }
        } else if ($request['is_subscription'] != $organization->is_subscription) {
            if (!$request['is_subscription'] && $organization->is_subscription) { // remove subscription
                Helper::deactivatePlanOnPaypal($organization->monthly_subscription_price_id);
                Helper::deactivatePlanOnPaypal($organization->yearly_subscription_price_id);
                // should send message to customers whether they will cancel current or upgrade to new
                // also send mail to admin for check this = should delete price & product
                $fields['monthly_subscription_price_id'] = null;
                $fields['yearly_subscription_price_id'] = null;
                $fields['yearly_subscription_amount'] = null;
                $fields['monthly_subscription_amount'] = null;
            }
            if ($request['is_subscription']) {
                // create subscription
                try {
                    $name = isset($fields['name']) ? $fields['name'] : $organization->name;
                    $slug = isset($fields['slug']) ? $fields['slug'] : $organization->slug;
                    $fields['is_subscription'] = 1;
                    $fields['subscription_product_id'] = Helper::createProductOnPaypal($name . "'s Subscription Product", $slug);
                    $fields['monthly_subscription_price_id'] = $fields['monthly_subscription_amount'] > 0 ? Helper::createMonthlyPlanOnPaypal(
                        $fields['subscription_product_id'],
                        $name . ' Monthly Subscription',
                        'Monthly Subscription (1 Month Trial)',
                        $fields['monthly_subscription_amount'],
                        $fields['currency']
                    ) : 'free-plan';
                    $fields['yearly_subscription_price_id'] = $fields['yearly_subscription_amount'] > 0 ? Helper::createYearlyPlanOnPaypal(
                        $fields['subscription_product_id'],
                        $name . ' Yearly Subscription',
                        'Yearly Subscription (1 Month Trial)',
                        $fields['yearly_subscription_amount'],
                        $fields['currency']
                    ) : 'free-plan';
                } catch (\Throwable $th) {
                    return $this->sendError($th->getMessage(), 422);
                }
            }
        }
        if (!$fields['is_crypto']) {
            $fields['wallet_address'] = null;
        }
        $organization->update($fields);
        $promo_code = $request->promo_code;
        $affiliate_email = $request->affiliate_email;
        $affiliate_percent = $request->affiliate_percent;
        $discount_percent = $request->discount_percent;

        if ($promo_code) {
            foreach ($promo_code as $key => $code) {
                $promo = PromoCode::where('organization_id', $organization->id)->where('promo_code', $code)->get()->first();
                if (!$promo) {
                    $promo = PromoCode::create([
                        'organization_id' => $organization->id,
                        'promo_code' => $code,
                        'affiliate_email' => $affiliate_email[$key],
                        'affiliate_percent' => $affiliate_percent[$key],
                        'discount_percent' => $discount_percent[$key],
                    ]);
                    Notification::route('mail', $affiliate_email[$key])->notify(new PromoRegister($organization, $code));
                }
            }
        }
        return $this->sendResponse($organization, 'success');
    }

    public function deleteOrganization(Organization $organization)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $organization = $user->organizations()->where('slug', $organization->slug)->firstOrFail();
        Event::where('organization_id', $organization->id)->delete();
        $organization->delete();
        return $this->sendResponse(null, 'success');
    }

    public function getPromo(Organization $organization)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization)) {
            return $this->sendError('No Permission', 401);
        }
        $promo = $organization->promo_codes;
        return $this->sendResponse($promo, 'success');
    }

    public function createPromo(Organization $organization, Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization)) {
            return $this->sendError('No Permission', 401);
        }
        $fields = $this->validate($request, [
            'promo_code' => 'required',
            'affiliate_email' => 'nullable',
            'affiliate_percent' => 'nullable',
            'discount_percent' => 'nullable',
            'bank_first_name' => 'nullable',
            'bank_last_name' => 'nullable',
            'bank_business_name' => 'nullable',
            'bank_business_email' => 'nullable',
            'bank_routing_number' => 'nullable',
            'bank_account_number' => 'nullable',
            'zelle_email' => 'nullable',
            'zelle_phone_number' => 'nullable',
        ]);
        // Set default values if null
        $fields['affiliate_percent'] = $fields['affiliate_percent'] ?? 0;
        $fields['discount_percent'] = $fields['discount_percent'] ?? 0;
        try {
            if (PromoCode::where('organization_id', $organization->id)->where('promo_code', $fields['promo_code'])->first()) {
                return $this->sendError('Promo Code already exist!', 422);
            }
            $fields['organization_id'] = $organization->id;
            $promo = PromoCode::create($fields);
            return $this->sendResponse($promo, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    public function updatePromo(Organization $organization, PromoCode $promo, Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization) || $promo->organization_id !== $organization->id) {
            return $this->sendError('No Permission', 401);
        }
        $fields = $this->validate($request, [
            'promo_code' => 'nullable',
            'affiliate_email' => 'nullable',
            'affiliate_percent' => 'nullable',
            'discount_percent' => 'nullable',
            'bank_first_name' => 'nullable',
            'bank_last_name' => 'nullable',
            'bank_business_name' => 'nullable',
            'bank_business_email' => 'nullable',
            'bank_routing_number' => 'nullable',
            'bank_account_number' => 'nullable',
            'zelle_email' => 'nullable',
            'zelle_phone_number' => 'nullable',
        ]);
        try {
            if (isset($fields['promo_code']) && $promo->promo_code !== $fields['promo_code'] && PromoCode::where('promo_code', $fields['promo_code'])->first()) {
                return $this->sendError('Promo Code already exist!', 422);
            }
            $promo->update($fields);
            return $this->sendResponse($promo, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 'success');
        }
    }

    public function deletePromo(Organization $organization, PromoCode $promo)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization) || $promo->organization_id !== $organization->id) {
            return $this->sendError('No Permission', 401);
        }
        try {
            $promo->delete();
            return $this->sendResponse(null, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 'success');
        }
    }

    public function sendPromoBankEmail(Organization $organization, PromoCode $promo)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization) || $promo->organization_id !== $organization->id) {
            return $this->sendError('No Permission', 401);
        }
        try {
            if ($promo->affiliate_email) {
                Notification::route('mail', $promo->affiliate_email)->notify(new PromoRegister($organization, $promo->promo_code));
            }
            return $this->sendResponse(null, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 'success');
        }
    }
}
