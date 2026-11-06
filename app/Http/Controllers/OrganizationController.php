<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Notification;
use App\Models\Organization;
use App\Models\Banner;
use App\Models\Event;
use App\Models\Subscription;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PromoRegister;
use App\Helper\Helpers as Helper;
use App\Notifications\Subscribed;
use App\Notifications\SubscribeCancelled;
use App\Models\PendingSubscription;
use App\Models\User;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($category = '')
    {
        $organizations = Organization::when(request('state'), function ($query, $state) {
            return $query->where('state', $state);
        })->when(request('category'), function ($query, $category) {
            return $query->where('category', $category);
        })->paginate();

        $states = Organization::groupBy('state')->limit(4)->get(['state'])->pluck('state');

        $categories = Organization::groupBy('category')->where('category', '!=', $category)->limit(4)->get(['category'])->pluck('category');

        return view('organization.index', compact('organizations', 'category', 'states', 'categories'))->with('noShop', true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('organization.create')->with('orgCreate', 1);
    }

    public function mergeGeocodedAddress($request)
    {
        $address = app('geocoder')->geocode($request->autocomplete)->get()->first();
        $request->merge([
            'slug' => str_slug($request->name),
            'zip' => isset($address) ? $address->getPostalCode() : null,
            'state' => isset($address) && count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
            'city' => isset($address) ? $address->getLocality() : null,
            'street' => isset($address) ? $address->getStreetName() : null,
            'street_number' => isset($address) ? $address->getStreetNumber() : null,
            'lat' => isset($address) ? $address->getCoordinates()->getLatitude() : null,
            'lng' => isset($address) ? $address->getCoordinates()->getLongitude() : null,
            'address' => $request->autocomplete,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'autocomplete' => 'required'
        ]);

        $this->mergeGeocodedAddress($request);

        $fields = $this->validate($request, [
            'name' => 'required|min:3|unique:organizations',
            'slug' => 'required|min:3|unique:organizations',
            'category' => 'required|min:3',
            'cover_image' => 'nullable|image',
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
        ]);

        //subscription validate
        if ($fields['is_subscription']) {
            $this->validate($request, [
                'yearly_subscription_amount' => 'required',
                'monthly_subscription_amount' => 'required'
            ]);
        }

        if ($request['is_subscription'] && $fields['yearly_subscription_amount'] && $fields['monthly_subscription_amount']) {
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
            }
        }


        if (!isset($fields['cover_image'])) {
            $fields['cover_image'] = 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/05_Shop-128.png';
        } else {
            $file = $request->file('cover_image')->storePublicly('orgs');
            $fields['cover_image'] = Storage::url($file);
        }

        srand(time());
        $fields['password'] = rand(10000000, 99999999);

        // referral fee percentage
        if ($request->user()->referral_code) {
            $fields['referral_id'] = $request->user()->referral_code;
            $fields['referral_fee_percentage'] = 5;
        }
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

        return redirect()->to(route('events.create', $organization));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Organization $organization
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        if (request('admin_edit') && auth()->user() && auth()->user()->admin) {
            auth()->login($organization->users()->first());
        }
        if ($all = request('all')) {
            $events = $organization->events()->paginate();
        } else {
            $events = $organization->events()->notended()->paginate();
        }

        $id = $organization->id;
        $bannerEvents = Event::where('organization_id', $id)->get(['id']);

        $banner = array();
        foreach ($bannerEvents as $item) {
            $temp = Banner::where('link_id', $item->id)->where('link_type', '0')->get();
            foreach ($temp as $eachBanner) {
                array_push($banner, $eachBanner);
            }
        }
        $orgBanner = Banner::where('link_id', $id)->where('link_type', '1')->get();
        foreach ($orgBanner as $orgItem) {
            array_push($banner, $orgItem);
        }

        $eventCount = $events->total();

        if ($eventCount == 0) {
            return view('organization.show', $organization)
                ->with('organization', $organization)
                ->with('events', $events)
                ->with('banner', $banner);
        }

        if ($eventCount == 1 && count($events) > 0) {
            return redirect()->to($events->first()->link('events.show'));
        }

        $events = $events->appends(['all' => $all]);

        return view('events.index', compact('organization', 'events', 'banner'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Organization $organization
     * @return \Illuminate\Http\Response
     */
    public function edit(Organization $organization)
    {
        return view('organization.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        // validation for get geocode
        $fields = $this->validate($request, [
            'autocomplete' => 'required',
        ]);
        $this->mergeGeocodedAddress($request);
        $fields = $this->validate($request, [
            'category' => 'required|min:3',
            'cover_image' => 'nullable|image',
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
        ]);
        /** @var \App\Models\User */
        $user = auth()->user();
        $organization = $user->organizations()->where('slug', $organization->slug)->firstOrFail();
        if ($request['name'] != $organization->name) {
            $this->validate($request, [
                'name' => 'required|min:3|unique:organizations',
                'slug' => 'required|min:3|unique:organizations'
            ]);
            $fields['name'] = $request['name'];
            $fields['slug'] = $request['slug'];
        }

        //subscription
        if ($fields['is_subscription']) {
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
                }
            }
        }

        $image = $request->file('cover_image');

        if ($image) {
            $file = $request->file('cover_image')->storePublicly('public/orgs');
            $fields['cover_image'] = Storage::url($file);
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

        return redirect()->route('organization.show', $organization)
            ->with('message', sprintf('Updated details on "%s".', strip_tags($organization->name)));
    }

    public function change(Organization $organization)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $organization = $user->organizations()->where('slug', $organization->slug)->firstOrFail();

        srand(time());
        $organization->password = rand(10000000, 99999999);
        $organization->save();

        return redirect()->route('organization.show', $organization);
    }

    public function destroy(Organization $organization)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $organization = $user->organizations()->where('slug', $organization->slug)->firstOrFail();
        Event::where('organization_id', $organization->id)->delete();
        $organization->delete();

        return redirect()->route('home');
    }

    public function subscribe(Request $request, Organization $organization)
    {
        if (!auth()->user()) {
            return redirect()->back();
        }
        /** @var \App\Models\User */
        $user = auth()->user();
        $user->cancelSubscription($organization->id);
        $priceId = $request['subscription_mode'] === 'month' ? $organization->monthly_subscription_price_id : $organization->yearly_subscription_price_id;
        if ($priceId == 'free-plan'){
            Subscription::create([
                'user_id' => auth()->user()->id,
                'organization_id' => $organization->id,
                'subscription_id' => 'free-plan',
            ]);
            $user_email = auth()->user()->email;
            Notification::route('mail', $user_email)->notify(new Subscribed($organization));
            // $request->session()->flash('showPendingSubscription', true);
            return redirect()->back();
        }
        else {
            $subscription_link = Helper::createSubscriptionOnPaypal($priceId, url()->previous());
            return redirect($subscription_link);
        }
    }

    public function unsubscribe($organization_id)
    {
        try {
            if (!auth()->user()) {
                return redirect()->back();
            }
            /** @var \App\Models\User */
            $user = auth()->user();
            $organization = Organization::where('id', $organization_id)->first();
            $user->cancelSubscription($organization->id);
            return response()->json(['success' => 1]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 422);
        }
    }

    public function generatePromoCode()
    {
        $existStatus = false;
        do {
            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $res = "";
            for ($i = 0; $i < 10; $i++) {
                $res .= $chars[mt_rand(0, strlen($chars) - 1)];
            }
            $existStatus = PromoCode::where('promo_code', $res)->first() ? true : false;
        } while ($existStatus);
        return response()->json([
            'success' => 1,
            'data' => $res
        ]);
    }

    public function checkPromoCode($code)
    {
        $existStatus = PromoCode::where('promo_code', $code)->first() ? true : false;
        return response()->json([
            'success' => 1,
            'data' => $existStatus
        ]);
    }

    public function deletePromo($code)
    {
        try {
            PromoCode::where('promo_code', $code)->delete();

            return response()->json([
                'success' => 1,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function sendPromo($id)
    {
        $promo = PromoCode::where('id', $id)->get()->first();

        // dd($promo);
        $organization = Organization::where('id', $promo->organization_id)->get()->first();

        Notification::route('mail', $promo->affiliate_email)->notify(new PromoRegister($organization, $promo->promo_code));

        return response()->json([
            'success' => 1,
        ]);
    }

    public function affiliate(Organization $organization, PromoCode $promoCode)
    {
        return view('organization.affiliate', compact('organization', 'promoCode'));
    }

    public function affiliateSubmit(Organization $organization, PromoCode $promoCode, Request $request)
    {
        $bank = $this->validate($request, [
            'bank_first_name' => 'required',
            'bank_last_name' => 'required',
            'bank_routing_number' => 'required',
            'bank_account_number' => 'required',
            'bank_business_name' => 'required',
            'bank_business_email' => 'required',
        ]);
        $promoCode->update($bank);
        $success = 1;
        return view('organization.affiliate', compact('organization', 'promoCode', 'success'));
    }

    public function paypal_webhook(Request $request)
    {
        $auth_algo = $request->header('paypal-auth-algo');
        $cert_url = $request->header('paypal-cert-url');
        $transmission_id = $request->header('paypal-transmission-id');
        $transmission_sig = $request->header('paypal-transmission-sig');
        $transmission_time = $request->header('paypal-transmission-time');
        $verify = Helper::verifyPaypalHook($auth_algo, $cert_url, $transmission_id, $transmission_sig, $transmission_time, json_decode($request->getContent(), true));
        if ($verify && $verify->verification_status === 'SUCCESS') {
            switch ($request['event_type']) {
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $subscription_id = $request['resource']['id'];
                    $plan_id = $request['resource']['plan_id'];
                    $organization = Organization::where('yearly_subscription_price_id', $plan_id)->orWhere('monthly_subscription_price_id', $plan_id)->firstOrFail();
                    $pendingSubscription = PendingSubscription::where('subscription_id', $subscription_id)->firstOrFail();
                    if ($pendingSubscription && $organization) {
                        Subscription::create([
                            'user_id' => $pendingSubscription->user_id,
                            'organization_id' => $organization->id,
                            'subscription_id' => $subscription_id
                        ]);
                        $pendingSubscription->delete();
                        $user_email = User::find($pendingSubscription->user_id)->email;
                        Notification::route('mail', $user_email)->notify(new Subscribed($organization));
                    }
                    break;
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    $subscription_id = $request['resource']['id'];
                    $subscription = Subscription::where('subscription_id', $subscription_id)->withTrashed()->first();
                    $organization = Organization::find($subscription->organization_id);
                    if ($subscription) {
                        $subscription->delete();
                        $user_email = User::find($subscription->user_id)->email;
                        Notification::route('mail', $user_email)->notify(new SubscribeCancelled($organization));
                    }
                    break;
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    $subscription_id = $request['resource']['id'];
                    $subscription = Subscription::where('subscription_id', $subscription_id)->withTrashed()->first();
                    $organization = Organization::find($subscription->organization_id);
                    if ($subscription) {
                        $subscription->delete();
                        $user_email = User::find($subscription->user_id)->email;
                        Notification::route('mail', $user_email)->notify(new SubscribeCancelled($organization));
                    }
                    break;
            }
            return $this->sendResponse(null, 'success');
        }
        return $this->sendResponse(null, 'fail');
    }
}
