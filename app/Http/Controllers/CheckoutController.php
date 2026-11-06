<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Stripe;
use App\Models\Ticket;
use App\Models\VoucherBundle;
use App\Models\PromoCode;
use App\Models\CartHistory;
use Illuminate\Http\Request;
use App\Http\Requests\ChargeRequest;
use Illuminate\Validation\ValidationException;
use App\Notifications\UserCreated;
use Illuminate\Support\Facades\Hash;
use App\Models\Helpers;

use App\Notifications\TicketsAvailable;
use App\Notifications\CallTicketsAvailable;
use App\Notifications\ReservationTicketsAvailable;

use Firebase\JWT\JWT;
use Carbon\Carbon;


class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function decide()
    {
        $ticket = Ticket::findOrFail(request('id'));
        $quantity = intval(request('quantity'));

        if (auth()->check()) {
            return redirect()->route('checkout.create', [
                'type' => 'user',
                'id' => $ticket->id,
                'quantity' => $quantity,
            ]);
        }

        return view('checkouts.decide')
            ->with('ticket', $ticket)
            ->with('quantity', $quantity)
            ->with('noShop', true);
    }

    public function create($type = 'guest')
    {
        $ticket = Ticket::findOrFail(request('id'));
        if (!$ticket->event) {
            return redirect()->back();
        }
        $organization = $ticket->event->organization;
        $bundles = $ticket->bundles;
        $quantity = intval(request('quantity'));

        if ($quantity <= 0) {
            $quantity = 1;
        }

        $user = auth()->user();

        $call_start_times = [];
        if ($ticket->is_call) {
            $call_start_times = VoucherBundle::where('ticket_id', $ticket->id)->whereNotNull('call_start_time')->select('call_start_time')->get();
        }
        $reservation_start_times = [];
        if ($ticket->is_reservation) {
            $reservation_start_times = VoucherBundle::where('ticket_id', $ticket->id)->whereNotNull('reservation_start_time')->select('reservation_start_time')->get();
        }
        return view('checkouts.create', compact('organization', 'ticket', 'bundles', 'quantity', 'user', 'call_start_times', 'reservation_start_times'));
    }

    public function cartCheckout(Request $request)
    {
        $promo_code = $request->promo_code;
        $promoCode = null;
        if ($promo_code) {
            $promoCode = PromoCode::where('promo_code', $promo_code)->first();
        }
        $cartItems = \Cart::getContent();

        if (\Cart::getTotalQuantity() === 0) {
            return redirect()->route('events.index');
        }
        $this->validate($request, [
            'fname' => 'nullable',
            'lname' => 'nullable',
            'email' => 'required|email',
            'phone_number' => 'nullable',
            'agrees_to_terms' => 'required|boolean',
            'payment_method' => 'nullable'
        ]);
        if (\Cart::getSubTotal() > 0) {
            $this->validate($request, [
                'nonce' => 'required'
            ]);
        }
        $user = $request->user();

        if (!$request->agrees_to_terms) {
            return back()->withErrors(['agrees_to_terms' => 'You must agree to the terms and conditions.']);
        }
        if (!$user) {
            $this->validate($request, [
                'email' => 'required',
                'fname' => 'required',
                'lname' => 'required',
                'phone_number' => 'required'
            ]);
            if (User::where('email', $request->email)->first()) {
                return back()->withErrors(['email' => 'That email is already in use; please login before continuing.']);
            }

            $random_pwd = str_random(16);

            $user = User::create([
                'name' => $request->fname . ' ' . $request->lname,
                'email' => $request->email,
                'fname' => $request->fname,
                'lname' => $request->lname,
                'password' => Hash::make($random_pwd),
                'phone_number' => $request->phone_number,
            ]);

            $user->notify(new UserCreated($random_pwd));

            auth()->login($user);
        }
        if (\Cart::getSubTotal() > 0) {

            foreach ($cartItems as $item) {
                if ($promoCode) {
                    $ticket = Ticket::findOrFail($item->id);
                    if ($ticket->event->organization->id === $promoCode->organization_id && $promoCode->discount_percent) {
                        $discount = new \Darryldecode\Cart\CartCondition(array(
                            'name' => $promoCode->promo_code,
                            'type' => 'discount',
                            'value' => '-' . $promoCode->discount_percent . '%',
                        ));
                        \Cart::addItemCondition($item->id, $discount);
                    }
                }
                if ($item->attributes->currency !== 'USD') {
                    \Cart::update($item->id, array(
                        'price' => $item->price * Helpers::getCurrencyRate($item->attributes->currency)
                    ));
                }
            }
            $amount = intval(ceil(\Cart::getSubTotal() * 100 * 1.06));
            $privateKey = str_replace('||', PHP_EOL, env('POYNT_PRIVATE_KEY'));
            $payload = [
                'exp' => time() + 3600,
                'iat' => time(),
                'iss' => env('POYNT_APP_ID'),
                'sub' => env('POYNT_APP_ID'),
                'aud' => env('POYNT_API_URL'),
                'jti' => env('POYNT_BUSINESS_ID')
            ];
            $jwt = JWT::encode($payload, $privateKey, 'RS256');
            // get access token
            $client = new \GuzzleHttp\Client();
            try {
                $response = $client->post(env('POYNT_API_URL') . '/token', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'api-version' => '1.2',
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'form_params' => [
                        "grantType" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
                        "assertion" => $jwt
                    ]
                ]);
                $response = \json_decode($response->getBody()->getContents());
                $accessToken = $response->accessToken;
                $request_id = time();
                // get Card token
                $response = $client->post(env('POYNT_API_URL') . '/businesses/' . env('POYNT_BUSINESS_ID') . '/cards/tokenize', [
                    'headers' => [
                        'Authorization' => 'bearer ' . $accessToken,
                        'Poynt-Request-Id' => $request_id,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => '{
                        "nonce":"' . $request->nonce . '"
                    }'
                ]);
                $response = \json_decode($response->getBody()->getContents());
                if ($response->status === 'ACTIVE') {
                    $card_token = $response->token;
                    // charge card
                    $response = $client->post(env('POYNT_API_URL') . '/businesses/' . env('POYNT_BUSINESS_ID') . '/cards/tokenize/charge', [
                        'headers' => [
                            'Authorization' => 'bearer ' . $accessToken,
                            'Poynt-Request-Id' => $request_id,
                            'Content-Type' => 'application/json'
                        ],
                        'body' => json_encode([
                            "action" => "SALE",
                            "partialAuthEnabled" => false,
                            "context" => [
                                "businessId" => env('POYNT_BUSINESS_ID'),
                                "storeId" => env('POYNT_STORE_ID'),
                                "storeDeviceId" => env('POYNT_STORE_DEVICE_ID'),
                            ],
                            "amounts" => [
                                "transactionAmount" => $amount,
                                "orderAmount" => $amount,
                                "currency" => 'USD'
                            ],
                            "fundingSource" => [
                                "cardToken" => $card_token
                            ],
                            "emailReceipt" => false
                        ])
                    ]);
                    $response = \json_decode($response->getBody()->getContents());
                    if ($response->id && $response->status !== 'DECLINED') {
                        // if success
                        $charge_id = $response->id;
                        CartHistory::create([
                            'cart_detail' => \Cart::getContent()->toJson(),
                            'user_id' => $user->id
                        ]);
                    } else {
                        foreach ($cartItems as $item) {
                            \Cart::clearItemConditions($item->id);
                        }
                        return back()->withErrors(['card' => 'There is an error on your card transaction!']);
                    }
                } else {
                    foreach ($cartItems as $item) {
                        \Cart::clearItemConditions($item->id);
                    }
                    return back()->withErrors(['card' => 'We can not perform this card transaction!']);
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                foreach ($cartItems as $item) {
                    \Cart::clearItemConditions($item->id);
                }
                return back()->withErrors(['card' => 'We can not perform this card transaction!']);
            }
        }
        foreach ($cartItems as $item) {
            $ticket = Ticket::findOrFail($item->id);
            $bundle = $ticket->bundles[0];
            $bundle_price = $bundle['price'] / $bundle['count'];
            if ($promoCode && $ticket->event->organization->id === $promoCode->organization_id) {
                $bundle_price = $bundle_price * (100 - $promoCode->discount_percent) * .01;
            }
            $detail_amount = $bundle_price > 0 ? ($bundle_price * 1.06 * (1 - 0.03) - 25) * (1 - $ticket->event->organization->app_fee_percentage * .01) - 50 - $bundle_price * .06 : 0;
            $affiliate_amount = 0;
            if ($promoCode && $ticket->event->organization->id === $promoCode->organization_id) {
                $detail_amount = $detail_amount * (100 - $promoCode->affiliate_percent) * .01;
                $affiliate_amount = $detail_amount * $promoCode->affiliate_percent * .01;
            }
            if (!isset($charge_id)) {
                $charge_id = time();
            }
            for ($i = 1; $i <= $item->quantity; $i++) {
                $voucher_bundle = VoucherBundle::forceCreate([
                    'user_id' => $user->id,
                    'ticket_id' => $ticket->id,
                    'ticket_bundle_id' => $bundle['id'],
                    'charge_id' => $charge_id,
                    'detail_amount' => $detail_amount,
                    'detail_text' => $ticket->name . " Ticket $i of $item->quantity : " . $bundle['count'] . "-Bundle",
                    'payment_method' => $request->payment_method,
                    'upgrade_charge_id' => $charge_id
                ]);
                $voucher_bundle->generateCode();
                if ($promoCode && $ticket->event->organization->id === $promoCode->organization_id) {
                    if (isset($affiliate_amount) && $affiliate_amount > 0) {
                        $voucher_bundle->affiliate_amount = $affiliate_amount;
                        $voucher_bundle->affiliate_status = 1;
                    }
                    $voucher_bundle->promo_code_org_id = $promoCode->id;
                    $voucher_bundle->save();
                }
            }
            $ticket->quantity -= $item->quantity;
            $ticket->save();
        }
        $user->notify(new TicketsAvailable($user, $ticket->event->organization));
        \Cart::clear();
        $request->session()->put('purchase-complete', '1');
        return redirect("/tickets/");
    }

    public function store(ChargeRequest $request, Stripe $stripe)
    {
        $promo_code = $request->promo_code;
        $ticket = $request->ticket();
        if (!$request->payment_method && $ticket->cents > 0) {
            return back();
        }
        if ($ticket->is_donation && (!$request['donation_amount'] && $request['donation_amount'] == '')) {
            return back()->withErrors(['donation_warning' => 'You must input donation amount']);
        }
        if ($ticket->is_donation && ($request['donation_amount'] < 5)) {
            return back()->withErrors(['donation_warning' => 'donation amount must be over than 5$']);
        }
        $bundles_ids = $request->bundle_ids;
        $bundle_purchases = $request->bundle_purchases;
        $quantity = $request->quantity();

        $user = $request->user();
        $app_fee_percentage = $request->ticket()->event->organization->app_fee_percentage;

        if ($quantity == 0) {
            return back()->withErrors(['quantity' => 'You must add quantity.']);
        }
        if ($ticket->event->organization->category == "Political") {

            if (!$request->address) {
                return back()->withErrors(['address' => 'You must add address.']);
            }
            if (!$request->employer) {
                return back()->withErrors(['address' => 'You must add employer.']);
            }
            if (!$request->occupation) {
                return back()->withErrors(['address' => 'You must add occupation.']);
            }
            if ($ticket->event->statement && !$request->agrees_to_states) {
                return back()->withErrors(['agrees_to_states' => 'You must certify the following statement.']);
            }
        }

        if ($ticket->is_donation) {
            $quantity = 0;
        }

        if ($ticket->is_call) {
            if (!isset($request->call_time)) {
                return back()->withErrors(['call_ticket_warning' => 'You must choose book time.']);
            }
            $call_start_time = substr($request->call_time, 0, 5);
            $call_start_date = $request->call_date . " " . $call_start_time;
        }

        if ($ticket->is_reservation) {
            if (!isset($request->reservation_time)) {
                return back()->withErrors(['reservation_ticket_warning' => 'You must choose reservation time.']);
            }
            $reservation_start_time = substr($request->reservation_time, 0, 5);
            $reservation_start_date = $request->reservation_date . " " . $reservation_start_time;
        }

        if (!$request->agrees_to_terms) {
            return back()->withErrors(['agrees_to_terms' => 'You must agree to the terms and conditions.']);
        }

        if ($ticket->event->theatre_id && $ticket->event->theatre_json && $ticket->is_seat && (!$request['selectedSeats'] || count(json_decode($request['selectedSeats'])) < 1 || count(json_decode($request['selectedSeats'])) < $quantity)) {
            return back()->withErrors(['seat' => 'You must choose seat.']);
        }

        if (!$user) {
            $this->validate($request, [
                'email' => 'required',
                'fname' => 'required',
                'lname' => 'required',
                // 'phone_number' => 'required'
            ]);
            if (User::where('email', $request->email())->first()) {
                return back()->withErrors(['email' => 'That email is already in use; please login before continuing.']);
            }

            $random_pwd = str_random(16);

            $user = User::create([
                'name' => $request->fname() . ' ' . $request->lname(),
                'email' => $request->email(),
                'fname' => $request->fname(),
                'lname' => $request->lname(),
                'password' => Hash::make($random_pwd),
                'phone_number' => $request->phone_number(),
            ]);

            $user->notify(new UserCreated($random_pwd));

            auth()->login($user);
        }

        // abort earlier if no tickets available
        if (!$ticket->is_donation && $ticket->quantity < $quantity && !$ticket->event->theatre_id && !$ticket->event->theatre_json) {
            return back()->withErrors([
                'quantity' => "Sorry, there aren't that many tickets available any longer."
            ])
                ->with('alert', 'Whoops!')
                ->with('type', 'danger')
                ->with('message', 'There were some errors.');
        }

        $organization = $ticket->event->organization;
        // from url reference
        $promo = PromoCode::where('organization_id', $organization->id)->where('promo_code', $promo_code)->get()->first();
        // input code on checkout page
        $code = PromoCode::where('organization_id', $organization->id)->where('promo_code', $request['code'])->get()->first();
        $discount = false;
        $discout_percent = 0;
        if (($promo && $promo['promo_code'] == $request['code']) || (!$promo && $code)) {
            $discount = true;
        }
        if (!$promo) {
            $promo = $code;
        }
        $bundles = [];
        if ($ticket->is_donation) {
            array_push($bundles, [
                'id' => $ticket->bundles[0]->id,
                'count' => 1,
                'price' => intval($request['donation_amount']) * 100,
                'ticket_type' => 'donation',
                'purchase_quantity' => 1
            ]);
        } else if ($ticket->is_call) {
            array_push($bundles, [
                'id' => $ticket->bundles[0]->id,
                'count' => 1,
                'price' => $discount ? intval($ticket->bundles[0]->price * (100 - $promo['discount_percent']) / 100) : $ticket->bundles[0]->price,
                'ticket_type' => 'call',
                'purchase_quantity' => 1
            ]);
        } else if ($ticket->is_reservation) {
            array_push($bundles, [
                'id' => $ticket->bundles[0]->id,
                'count' => 1,
                'price' => $discount ? intval($ticket->bundles[0]->price * (100 - $promo['discount_percent']) / 100) : $ticket->bundles[0]->price,
                'ticket_type' => 'reservation',
                'purchase_quantity' => 1
            ]);
        } else {
            foreach ($ticket->bundles as $b) {

                $purchase_quantity = 0;
                for ($i = 0; $i < count($bundles_ids); $i++) {
                    if ($bundles_ids[$i] == $b->id)
                        $purchase_quantity = $bundle_purchases[$i];
                }

                array_push($bundles, [
                    'id' => $b->id,
                    'count' => $b->count,
                    'price' => $discount ? intval($b->price * (100 - $promo['discount_percent']) / 100) : $b->price,
                    'ticket_type' => $b->ticket_type,
                    'purchase_quantity' => intval($purchase_quantity)
                ]);
            }
        }

        $selectedSeats = json_decode($request['selectedSeats']);
        if ($ticket->is_donation || $ticket->cents > 0) {
            $stripe->calculateWithBundles($ticket, $bundles, $app_fee_percentage);
            $voucher_bundle_ids = [];
            $current_voucher_count = 0;
            foreach ($bundles as $bundle) {
                if ($bundle['purchase_quantity'] == 0) continue;
                foreach (range(1, $bundle['purchase_quantity']) as $n) {
                    foreach (range(1, $bundle['count']) as $idx) {
                        if ($promo) {
                            $voucher_bundle = VoucherBundle::forceCreate([
                                'user_id' => $user->id,
                                'ticket_id' => $ticket->id,
                                'ticket_bundle_id' => $bundle['id'],
                                'charge_id' => 0,
                                'detail_amount' => $bundle['price'] / $bundle['count'],
                                'detail_text' => "Normal Ticket $n of {$bundle['purchase_quantity']} : " . $bundle['count'] . "-Bundle",
                                'affiliate_amount' => $promo->affiliate_percent ? $promo->affiliate_percent * $bundle['price'] / $bundle['count'] * 0.01 : 0,
                                'payment_method' => $request->payment_method,
                                'address' => $request->address,
                                'employer' => $request->employer,
                                'occupation' => $request->occupation,
                            ]);
                        } else {
                            $voucher_bundle = VoucherBundle::forceCreate([
                                'user_id' => $user->id,
                                'ticket_id' => $ticket->id,
                                'ticket_bundle_id' => $bundle['id'],
                                'charge_id' => 0,
                                'detail_amount' => $bundle['price'] / $bundle['count'],
                                'detail_text' => "Normal Ticket $n of {$bundle['purchase_quantity']} : " . $bundle['count'] . "-Bundle",
                                'affiliate_amount' => $ticket->affiliate_amount ? $ticket->affiliate_amount * $bundle['count'] * 100 : 0,
                                'payment_method' => $request->payment_method,
                                'address' => $request->address,
                                'employer' => $request->employer,
                                'occupation' => $request->occupation,
                            ]);
                        }
                        if ($ticket->is_call) {
                            $voucher_bundle->call_start_time = $call_start_date;
                        }
                        if ($ticket->is_reservation) {
                            $voucher_bundle->reservation_start_time = $reservation_start_date;
                        }
                        if (request()->adsid) {
                            $voucher_bundle->adsid = request()->adsid;
                        }
                        if ($ticket->event->theatre_id && $ticket->event->theatre_json && $selectedSeats && count($selectedSeats) > 0) {
                            $seat_json = [];
                            foreach (range(0, $bundle['count'] - 1) as $index) {
                                array_push($seat_json, $selectedSeats[$index + $current_voucher_count]);
                            }
                            $voucher_bundle->seat_json = json_encode($seat_json);
                        }
                        $voucher_bundle->save();
                        $voucher_bundle->generateCode();
                        array_push($voucher_bundle_ids, $voucher_bundle->id);
                        $voucher_bundle->delete();
                        $current_voucher_count += $bundle['count'];
                    }
                }
            }
            if ($request->payment_method === 'card' || $ticket->is_donation) {
                //godaddy poynt card checkout
                VoucherBundle::withTrashed()->whereIn('id', $voucher_bundle_ids)->update(['upgrade_charge_id' => time()]);
                if ($ticket->is_donation) {
                    $amount = $stripe->amount / 100;
                    $tax = 0;
                } else {
                    $amount = $stripe->amount * 1.06 / 100;
                    $tax = $stripe->amount * 0.06 / 100;
                }
                $currency = $ticket->event->organization->currency;
                $voucher_bundle_id = $voucher_bundle_ids[0];
                $quantity = count($voucher_bundle_ids);
                $price = $stripe->amount / 100;
                return view('checkouts.cardCheckout', compact('amount', 'currency', 'voucher_bundle_id', 'ticket', 'quantity', 'price', 'tax'));
            } else if ($request->payment_method === 'crypto') {
                $amount = $stripe->amount / 100;
                try {
                    $client = new \GuzzleHttp\Client();
                    $response = $client->get('https://api.coinbase.com/v2/exchange-rates?currency=ETH');
                    $response = \json_decode($response->getBody()->getContents());
                    $crypto_rates = $response->data->rates->{$ticket->event->organization->currency};
                    return view('checkouts.cryptoCheckout', compact('voucher_bundle_ids', 'ticket', 'amount', 'crypto_rates'));
                } catch (\Throwable $th) {
                    return redirect()->back();
                }
            }
        } else {
            $emailUser = User::updateOrCreate(['email' => $request->email()]);
            if (!isset($call_start_date)) {
                $call_start_date = '';
            }
            if (!isset($reservation_start_date)) {
                $reservation_start_date = '';
            }
            $voucher_bundle = VoucherBundle::for($bundles[0]['purchase_quantity'])->createTickets($user, $ticket, $bundles[0], $emailUser, 0, 0, 0, 0, $call_start_date, $reservation_start_date, $selectedSeats);
            if (request()->adsid) {
                $voucher_bundle->adsid = request()->adsid;
                $voucher_bundle->save();
            }
            $voucher_bundle->address = $request->address;
            $voucher_bundle->employer = $request->employer;
            $voucher_bundle->occupation = $request->occupation;
            $voucher_bundle->save();
            $request->session()->flash('purchase-complete', '1');
            return redirect("/tickets/{$ticket->event->id}");
        }
    }

    public function chargePoynt(Request $request)
    {
        $data = $this->validate($request, [
            'nonce' => 'required',
            'voucher_bundle_id' => 'required'
        ]);
        // calculate amount
        $voucher = VoucherBundle::withTrashed()->where('id', $data['voucher_bundle_id'])->first();
        $theatre_json = $voucher->ticket->event->theatre_json;
        $vouchers = VoucherBundle::withTrashed()->where('upgrade_charge_id', $voucher->upgrade_charge_id)->get();
        $amount = 0;
        foreach ($vouchers as $voucher) {
            $amount += $voucher->detail_amount;
        }
        // calculate by adding Tax 6%
        $amount = intval(ceil($amount * 1.06));
        $privateKey = str_replace('||', PHP_EOL, env('POYNT_PRIVATE_KEY'));
        $payload = [
            'exp' => time() + 3600,
            'iat' => time(),
            'iss' => env('POYNT_APP_ID'),
            'sub' => env('POYNT_APP_ID'),
            'aud' => env('POYNT_API_URL'),
            'jti' => env('POYNT_BUSINESS_ID')
        ];
        $jwt = JWT::encode($payload, $privateKey, 'RS256');
        // get access token
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->post(env('POYNT_API_URL') . '/token', [
                'headers' => [
                    'Accept' => 'application/json',
                    'api-version' => '1.2',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    "grantType" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
                    "assertion" => $jwt
                ]
            ]);
            $response = \json_decode($response->getBody()->getContents());
            $accessToken = $response->accessToken;
            $request_id = time();
            // get Card token
            $response = $client->post(env('POYNT_API_URL') . '/businesses/' . env('POYNT_BUSINESS_ID') . '/cards/tokenize', [
                'headers' => [
                    'Authorization' => 'bearer ' . $accessToken,
                    'Poynt-Request-Id' => $request_id,
                    'Content-Type' => 'application/json'
                ],
                'body' => '{
                    "nonce":"' . $data['nonce'] . '"
                }'
            ]);
            $response = \json_decode($response->getBody()->getContents());
            if ($response->status === 'ACTIVE') {
                $card_token = $response->token;
                // charge card
                $response = $client->post(env('POYNT_API_URL') . '/businesses/' . env('POYNT_BUSINESS_ID') . '/cards/tokenize/charge', [
                    'headers' => [
                        'Authorization' => 'bearer ' . $accessToken,
                        'Poynt-Request-Id' => $request_id,
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode([
                        "action" => "SALE",
                        "partialAuthEnabled" => false,
                        "context" => [
                            "businessId" => env('POYNT_BUSINESS_ID'),
                            "storeId" => env('POYNT_STORE_ID'),
                            "storeDeviceId" => env('POYNT_STORE_DEVICE_ID'),
                        ],
                        "amounts" => [
                            "transactionAmount" => $amount,
                            "orderAmount" => $amount,
                            "currency" => $voucher->ticket->event->organization->currency
                        ],
                        "fundingSource" => [
                            "cardToken" => $card_token
                        ],
                        "emailReceipt" => false
                    ])
                ]);
                $response = \json_decode($response->getBody()->getContents());
                if ($response->id && $response->status !== 'DECLINED') {
                    // if success
                    $charge_id = $response->id;
                    $detail_amount = ($voucher->detail_amount * 1.06 * (1 - 0.03) - 25) * (1 - $voucher->ticket->event->organization->app_fee_percentage * .01) - 50 - $voucher->detail_amount * .06;
                    foreach ($vouchers as $voucher) {
                        $voucher->restore();
                        if ($voucher->seat_json && $theatre_json && $voucher->ticket->is_seat) {
                            $seats = \json_decode($voucher->seat_json);
                            if ($seats) {
                                foreach ($seats as $seat) {
                                    $_seat = $theatre_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1];
                                    $_seat['SeatStatus'] = "1";
                                    $_seat['selled_at'] = Carbon::now();
                                    $_seat['user_id'] = $voucher->user()->first()->id;
                                    $theatre_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1] = $_seat;
                                }
                            }
                        }
                        $voucher->charge_id = $charge_id;
                        $voucher->detail_amount = $detail_amount;
                        $voucher->save();
                    }
                    if ($theatre_json) {
                        $voucher->ticket->event->theatre_json = $theatre_json;
                        $voucher->ticket->event->save();
                    }
                    $voucher->ticket->quantity -= count($vouchers);
                    $voucher->ticket->save();
                    if ($voucher->ticket->is_call) {
                        $voucher->user()->first()->notify(new CallTicketsAvailable($voucher->user()->first(), $voucher));
                        $voucher->ticket->event->organization->users()->first()->notify(new CallTicketsAvailable($voucher->ticket->event->organization->users()->first(), $voucher));
                    } else if ($voucher->ticket->is_reservation) {
                        $voucher->user()->first()->notify(new ReservationTicketsAvailable($voucher->user()->first(), $voucher));
                        $voucher->ticket->event->organization->users()->first()->notify(new ReservationTicketsAvailable($voucher->ticket->event->organization->users()->first(), $voucher));
                    } else {
                        $voucher->user()->first()->notify(new TicketsAvailable($voucher->user()->first(), $voucher->ticket->event->organization));
                    }
                    return response()->json(['message' => 'success', 'success' => 1], 200);
                } else {
                    return response()->json(['error' => 'There is an error on your card transaction!'], 500);
                }
            } else {
                return response()->json(['error' => 'We can not perform this card transaction!'], 500);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json(['error' => 'We can not perform this card transaction!'], 500);
        }
    }
}
