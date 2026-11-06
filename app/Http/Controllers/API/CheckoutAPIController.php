<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketBundle;
use App\Models\VoucherBundle;
use Illuminate\Http\Request;
use App\Notifications\UserCreated;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PromoCode;
use App\Models\Helpers;
use App\Models\Event;
use App\Helper\Helpers as Helper;

use App\Notifications\TicketsAvailable;
use App\Notifications\CallTicketsAvailable;
use App\Notifications\ReservationTicketsAvailable;

use App\Notifications\TicketsReceiptWithQRAttach;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Carbon\Carbon;
use App\Models\Subscription;
use App\Models\PendingSubscription;

use Firebase\JWT\JWT;

class CheckoutAPIController extends Controller
{
    public function checkout(Request $request, Event $event)
    {
        try {
            if (!$request->user('sanctum')) {
                // In case Bearter Token is not corret or guest
                $request->validate([
                    'name' => 'required|max:255',
                    'email' => 'required|email',
                    'phone' => 'required',
                    'password' => 'required|max:100',
                    'cart_items' => 'present|array',
                    'selected_seats' => 'present|array'
                ]);
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    if (!auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                        return $this->sendError('An account is already registered with your email address. Please log in.', 401);
                    }
                } else {
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'phone_number' => $request->phone,
                        'fname' => explode(' ', $request->name) ? explode(' ', $request->name)[0] : $request->name,
                        'lname' => count(explode(' ', $request->name)) > 1 ? explode(' ', $request->name)[1] : $request->name,
                        'password' => Hash::make($request->password),
                    ]);

                    $user->notify(new UserCreated($request->password));
                    auth()->login($user);
                }
            } else {
                $request->validate([
                    'cart_items' => 'present|array',
                    'selected_seats' => 'present|array'
                ]);
            }
            $request->validate([
                'payment_method' => 'required' //paypal, nmi
            ]);
            if (count($request['cart_items']) === 0 && isset($request['selected_seats']) && count($request['selected_seats']) === 0) {
                return $this->sendError('Choose tickets to buy', 422);
            }
            if ($request->promo_code) {
                $promoCode = PromoCode::where('promo_code', $request->promo_code)->where('organization_id', $event->organization->id)->first();
                if ($promoCode) {
                    $promo = $promoCode;
                }
            }
            $user = $request->user('sanctum');
            $currency = $event->organization->currency;
            //calculate total
            $total = 0;
            $is_subscribed = false;
            if ($event->organization->is_subscription) {
                $subscription = Subscription::where('user_id', $user->id)->where('organization_id', $event->organization->id)->first();
                if ($subscription) {
                    $is_subscribed = true;
                }
            }
            try {
                foreach ($request['cart_items'] as $item) {
                    if (isset($item['is_bundle']) && $item['is_bundle']) {
                        $bundle = TicketBundle::findOrFail($item['bundle_id']);
                        if ($bundle->ticket->is_subscribe && $bundle->ticket->discount_percent && $is_subscribed) {
                            $price = $bundle->origin_price * (100 - $bundle->ticket->discount_percent) * .01;
                        } else {
                            $price = $bundle->origin_price;
                        }
                        $total += $price * $item['quantity'];
                    } else {
                        $ticket = Ticket::findOrFail($item['id']);
                        if ($ticket->is_donation) {
                            $price = $item['donation_amount'];
                        } else {
                            if ($ticket->is_subscribe && $ticket->discount_percent && $is_subscribed) {
                                $price = $ticket->origin_price * (100 - $ticket->discount_percent) * .01;
                            } else {
                                $price = $ticket->origin_price;
                            }
                        }
                        $total += $price * $item['quantity'];
                    }
                }
                if (isset($request['selected_seats']) && count($request['selected_seats']) > 0) {
                    $theatre_json = $event->theatre_json;
                    foreach ($request['selected_seats'] as $item) {
                        $price = Helpers::getSeatPriceByGUID($theatre_json, $item['seat']['seat_guid']);
                        // if ($currency !== 'USD') { //paypal
                        //     $price = $price * Helpers::getCurrencyRate($currency);
                        // }
                        $total += $price;
                    }
                }
            } catch (\Exception $e) {
                return $this->sendError($e->getMessage());
            }
            $charge_id = 'Free';
            if (isset($promo) && $promo->discount_percent && $total > 0) {
                $total = (100 - $promo->discount_percent) * .01 * $total;
                if ($total == 0) {
                    $charge_id = 'Free - Discount';
                }
            }
            if ($total > 0) {
                if ($request['payment_method'] === "nmi_ach") {
                    if ($currency !== 'USD') {
                        $total = $total * Helpers::getCurrencyRate($currency);
                    }
                    $request->validate([
                        'payment_token' => 'required',
                        'zipcode' => 'required',
                        'state' => 'required',
                        'country' => 'required',
                    ]);
                    $client = new \GuzzleHttp\Client();
                    try {
                        $response = $client->post(env('NMI_TRANSACT_ENDPOINT'), [
                            'headers' => [
                                'Content-Type' => 'application/x-www-form-urlencoded'
                            ],
                            'form_params' => [
                                "security_key" => env('NMI_SECURITY_KEY'),
                                "payment_token" => $request['payment_token'],
                                "payment" => "check",
                                // "checkname" => $request->checkname,
                                // "checkaba" => $request->check_routing_number,
                                // "checkaccount" => $request->check_account_number,
                                // "account_holder_type" => $request->account_holder_type,
                                // "account_type" => $request->account_type,
                                "sec_code" => "WEB",
                                'amount' => round($total * 1.06, 2),
                                'zip' => $request['zipcode'],
                                'state' => $request['state'],
                                'country' => $request['country'],
                                'first_name' => $user->fname ? $user->fname : $user->name,
                                'last_name' => $user->lname ? $user->lname : $user->name,
                            ]
                        ]);
                        parse_str($response->getBody()->getContents(), $result);
                        if ($result['response'] == 1 && $result['response_code'] == '100') {
                            $charge_id = $result['transactionid'];
                        } else {
                            return $this->sendError($result['responsetext'], 402);
                        }
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        return $this->sendError($e->getMessage());
                    }
                } else if ($request['payment_method'] === "nmi") {
                    if ($currency !== 'USD') {
                        $total = $total * Helpers::getCurrencyRate($currency);
                    }
                    $request->validate([
                        'payment_token' => 'required',
                        'zipcode' => 'required',
                        'state' => 'required',
                        'country' => 'required',
                        // 'nmi_3dsec' => 'required',
                    ]);
                    $client = new \GuzzleHttp\Client();
                    try {
                        if (env('APP_ENV') === 'local') {
                            $charge_id = 'local-nmi-test';
                        } else {
                            $response = $client->post(env('NMI_TRANSACT_ENDPOINT'), [
                                'headers' => [
                                    'Content-Type' => 'application/x-www-form-urlencoded'
                                ],
                                'form_params' => [
                                    "security_key" => env('NMI_SECURITY_KEY'),
                                    "payment_token" => $request['payment_token'],
                                    'amount' => round($total * 1.06, 2),
                                    'zip' => $request['zipcode'],
                                    'state' => $request['state'],
                                    'country' => $request['country'],
                                    'first_name' => $user->fname ? $user->fname : $user->name,
                                    'last_name' => $user->lname ? $user->lname : $user->name,
                                    // ...$request['nmi_3dsec']
                                ]
                            ]);
                            parse_str($response->getBody()->getContents(), $result);
                            if ($result['response'] == 1 && $result['response_code'] == '100') {
                                $charge_id = $result['transactionid'];
                            } else {
                                return $this->sendError($result['responsetext'], 402);
                            }
                        }
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        return $this->sendError($e->getMessage());
                    }
                } else if ($request['payment_method'] === 'poynt') {
                    $amount = intval(ceil($total * 1.06));
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
                            } else {
                                return $this->sendError('Your Card Payment is Declined!', 402);
                            }
                        } else {
                            return $this->sendError('We can not perform this card transaction!', 402);
                        }
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        return $this->sendError($e->getMessage());
                    }
                } else if ($request['payment_method'] === 'paypal') {
                    //paypal
                    $tax = .06 * $total;

                    $provider = new PayPalClient;
                    $provider->setApiCredentials(config('paypal'));
                    $provider->getAccessToken();
                    $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
                    $items = [];
                    foreach ($request['cart_items'] as $item) {
                        if (isset($item['is_bundle']) && $item['is_bundle']) {
                            $bundle = TicketBundle::findOrFail($item['bundle_id']);
                            $price = $bundle->origin_price;
                            if (!$bundle->ticket->is_donation) {
                                if (isset($promo) && $promo->discount_percent) {
                                    $price = (100 - $promo->discount_percent) * 0.01 * $price;
                                }
                                if ($bundle->ticket->is_subscribe && $bundle->ticket->discount_percent && $is_subscribed) {
                                    $price = $price * (100 - $bundle->ticket->discount_percent) * .01;
                                }
                            }
                            if ($currency !== 'USD') {
                                $price = $price * Helpers::getCurrencyRate($currency);
                            }
                            array_push($items, [
                                'name' => $bundle->bundle_name . ' at ' . $event->name,
                                'unit_amount' => [
                                    'currency_code' => 'USD',
                                    'value' => round($price, 2)
                                ],
                                'quantity' => $item['quantity']
                            ]);
                        } else {
                            $ticket = Ticket::findOrFail($item['id']);
                            // should improve this
                            if ($ticket->is_donation) {
                                $tax = 0;
                            }
                            $price = $ticket->origin_price;
                            if (isset($promo) && $promo->discount_percent) {
                                $price = (100 - $promo->discount_percent) * 0.01 * $price;
                            }
                            if ($ticket->is_subscribe && $ticket->discount_percent && $is_subscribed) {
                                $price = $price * (100 - $ticket->discount_percent) * .01;
                            }
                            if ($currency !== 'USD') {
                                $price = $price * Helpers::getCurrencyRate($currency);
                            }
                            array_push($items, [
                                'name' => $ticket->name . ' at ' . $event->name,
                                'unit_amount' => [
                                    'currency_code' => 'USD',
                                    'value' => round($price, 2)
                                ],
                                'quantity' => $item['quantity']
                            ]);
                        }
                    }
                    if (isset($request['selected_seats']) && count($request['selected_seats']) > 0) {
                        $theatre_json = $event->theatre_json;
                        foreach ($request['selected_seats'] as $item) {
                            $price = Helpers::getSeatPriceByGUID($theatre_json, $item['seat']['seat_guid']);
                            if (isset($promo) && $promo->discount_percent) {
                                $price = (100 - $promo->discount_percent) * 0.01 * $price;
                            }
                            if ($currency !== 'USD') {
                                $price = $price * Helpers::getCurrencyRate($currency);
                            }
                            array_push($items, [
                                'name' => $item['seat']['zone']['name'] . ' - Row ' . $item['seat']['row']['row_number'] . ' - Seat ' . $item['seat']['seat_number'] . '(' . $item['seat']['category'] . ')',
                                'unit_amount' => [
                                    'currency_code' => 'USD',
                                    'value' => round($price, 2)
                                ],
                                'quantity' => 1
                            ]);
                        }
                    }
                    $data = [
                        "intent" => "CAPTURE",
                        "purchase_units" => [
                            [
                                "amount" => [
                                    "currency_code" => 'USD',
                                    "value" => round(round($total, 2) + round($tax, 2), 2),
                                    "breakdown" => [
                                        "item_total" => [
                                            "currency_code" => 'USD',
                                            "value" => round($total, 2),
                                        ],
                                        "tax_total" => [
                                            "currency_code" => 'USD',
                                            "value" => round($tax, 2)
                                        ]
                                    ]
                                ],
                                'items' => $items
                            ]
                        ],
                        'application_context' => [
                            'return_url' => $tax == 0 ? env('FRONTEND_URL') . '/' . $event->organization->slug . '/' . $event->slug . '_' . $event->id : env('FRONTEND_URL') . '/pay-done',
                            'cancel_url' => env('FRONTEND_URL') . '/' . $event->organization->slug . '/' . $event->slug . '_' . $event->id
                        ]
                    ];
                    $order = $provider->createOrder($data);
                    if (isset($order['error'])) {
                        return $this->sendError($order['error']['details'][0]['description'], 422);
                    }
                    $charge_id = $order['id'];
                } else if ($request['payment_method'] === "stripe") {
                    //stripe
                    $tax = round(.06 * $total, 2);
                    $metadata = [];
                    foreach ($request['cart_items'] as $item) {
                        $ticket = Ticket::findOrFail($item['id']);
                        if ($ticket->is_donation) {
                            $price = $item['donation_amount'];
                            $tax = 0;
                        } else {
                            $price = $ticket->origin_price;
                            if (isset($promo) && $promo->discount_percent) {
                                $price = (100 - $promo->discount_percent) * $price;
                            }
                        }
                        array_push($metadata, json_encode([
                            'id' => $ticket->id,
                            'quantity' => $item['quantity'],
                            'price' => $price,
                            'currency' => $currency
                        ]));
                    }
                    if (isset($request['selected_seats']) && count($request['selected_seats']) > 0) {
                        $theatre_json = $event->theatre_json;
                        foreach ($request['selected_seats'] as $item) {
                            $price = Helpers::getSeatPriceByGUID($theatre_json, $item['seat']['seat_guid']);
                            if (isset($promo) && $promo->discount_percent) {
                                $price = (100 - $promo->discount_percent) * $price;
                            }
                            array_push($metadata, json_encode([
                                'seat_name' => $item['seat']['zone']['name'] . ' - Row ' . $item['seat']['row']['row_number'] . ' - Seat ' . $item['seat']['seat_number'] . '(' . $item['seat']['category'] . ')',
                                'price' => $price,
                                'currency' => $currency
                            ]));
                        }
                    }
                    $metadata['event'] = $event->organization->slug . '/' . $event->slug . '_' . $event->id;
                    $stripe = new \Stripe\StripeClient(env('STRIPE_PRIVATE_KEY'));
                    $customer = $stripe->customers->create([
                        'email' => $user->email,
                        'name' => $user->name,
                    ]);
                    $payment_intent = $stripe->paymentIntents->create([
                        'customer' => $customer->id,
                        'amount' => round($total * 100, 2) + $tax,
                        'currency' => $currency,
                        'automatic_payment_methods' => ['enabled' => true],
                        'metadata' => $metadata
                    ]);
                    $charge_id = $payment_intent['id'];
                }
            }
            $call_voucher = null;
            $reservation_voucher = null;
            $vouchers = [];
            foreach ($request['cart_items'] as $item) {
                if (isset($item['is_bundle']) && $item['is_bundle']) {
                    $bundle = TicketBundle::findOrFail($item['bundle_id']);
                    $detail_amount = $bundle->origin_price * 100 / $bundle->count;
                    if (isset($promo)) {
                        $detail_amount = $detail_amount * (100 - $promo->discount_percent) * .01;
                    }
                } else {
                    $ticket = Ticket::findOrFail($item['id']);
                    if ($ticket->is_donation) {
                        $detail_amount = $item['donation_amount'] * 100;
                    } else {
                        $detail_amount = $ticket->origin_price * 100;
                        if (isset($promo)) {
                            $detail_amount = $detail_amount * (100 - $promo->discount_percent) * .01;
                        }
                    }
                }
                $detail_amount = $detail_amount > 0 ? ($detail_amount * 1.06 * (1 - 0.029) - 30) * (1 - $event->organization->app_fee_percentage * .01) - 50 - $detail_amount * .06 : 0;
                $affiliate_amount = 0;
                if (isset($promo)) {
                    $detail_amount = $detail_amount * (100 - $promo->affiliate_percent) * .01;
                    $affiliate_amount = $detail_amount * $promo->affiliate_percent * .01;
                }
                $bundle_count = 1;
                if (isset($item['is_bundle']) && $item['is_bundle']) {
                    $ticket = Ticket::findOrFail($bundle->ticket_id);
                    $bundle_count = $bundle->count;
                }
                for ($i = 1; $i <= $item['quantity']; $i++) {
                    $fields = [
                        'user_id' => $user->id,
                        'ticket_id' => $ticket->id,
                        'charge_id' => $charge_id,
                        'detail_amount' => $detail_amount,
                        'detail_text' => isset($item['is_bundle']) && $item['is_bundle'] ? $ticket->name . ' ' . $bundle->bundle_name : $ticket->name,
                        'event_id' => $event->id,
                        'payment_method' => $request['payment_method'],
                        'adsid' => isset($request->adsid) ? $request->adsid : null,
                        'is_media' => $ticket->is_media,
                        'media_type' => $ticket->media_type,
                        'media_url' => $ticket->media_url,
                        'prompt_answer_json' => isset($item['prompt_answer_json']) && count(($item['prompt_answer_json'])) > 0 ? json_encode($item['prompt_answer_json']) : null
                    ];
                    for ($j = 0; $j < $bundle_count; $j++) {
                        if (isset($item['selected_date'])) {
                            if ($ticket->is_call) {
                                $fields['call_start_time'] = new Carbon($item['selected_date']);
                                $voucher_bundle = VoucherBundle::forceCreate($fields);
                                $call_voucher = $voucher_bundle;
                            }
                            if ($ticket->is_reservation) {
                                $fields['reservation_start_time'] = new Carbon($item['selected_date']);
                                $voucher_bundle = VoucherBundle::forceCreate($fields);
                                $reservation_voucher = $voucher_bundle;
                            }
                        } else
                            $voucher_bundle = VoucherBundle::forceCreate($fields);
                        $voucher_bundle->generateCode();
                        if (isset($promo)) {
                            if (isset($affiliate_amount) && $affiliate_amount > 0) {
                                $voucher_bundle->affiliate_amount = $affiliate_amount;
                                $voucher_bundle->affiliate_status = 1;
                            }
                            $voucher_bundle->promo_code_org_id = $promo->id;
                            $voucher_bundle->save();
                        }
                        if ($detail_amount > 0 && ($request['payment_method'] !== "nmi" && $request['payment_method'] !== "nmi_ach"))
                            $voucher_bundle->delete();
                        $vouchers[] = $voucher_bundle;
                    }
                }
                if (!$ticket->is_donation) {
                    $ticket->quantity -= $item['quantity'] * $bundle_count;
                    $ticket->save();
                }
            }
            if (isset($request['selected_seats']) && count($request['selected_seats']) > 0) {
                $theatre_json = $event->theatre_json;
                foreach ($request['selected_seats'] as $item) {
                    $price = Helpers::getSeatPriceByGUID($theatre_json, $item['seat']['seat_guid']);
                    $detail_amount = $price * 100;
                    if (isset($promo)) {
                        $detail_amount = $detail_amount * (100 - $promo->discount_percent) * .01;
                    }
                    $detail_amount = $detail_amount > 0 ? ($detail_amount * 1.06 * (1 - 0.03) - 25) * (1 - $event->organization->app_fee_percentage * .01) - 50 - $detail_amount * .06 : 0;
                    $affiliate_amount = 0;
                    if (isset($promo)) {
                        $detail_amount = $detail_amount * (100 - $promo->affiliate_percent) * .01;
                        $affiliate_amount = $detail_amount * $promo->affiliate_percent * .01;
                    }
                    $fields = [
                        'user_id' => $user->id,
                        'seat_guid' => $item['seat']['seat_guid'],
                        'seat_price' => $price * 100,
                        'event_id' => $event->id,
                        'charge_id' => $charge_id,
                        'detail_amount' => $detail_amount,
                        'detail_text' => $item['seat']['zone']['name'] . ' - Row ' . $item['seat']['row']['row_number'] . ' - Seat ' . $item['seat']['seat_number'] . '(' . $item['seat']['category'] . ')',
                        'payment_method' => $request['payment_method'],
                        'adsid' => isset($request->adsid) ? $request->adsid : null,
                        'ticket_type' => 'seat'
                    ];
                    $voucher_bundle = VoucherBundle::forceCreate($fields);
                    $voucher_bundle->generateCode();
                    if (isset($promo)) {
                        if (isset($affiliate_amount) && $affiliate_amount > 0) {
                            $voucher_bundle->affiliate_amount = $affiliate_amount;
                            $voucher_bundle->affiliate_status = 1;
                        }
                        $voucher_bundle->promo_code_org_id = $promo->id;
                        $voucher_bundle->save();
                    }
                    if ($detail_amount > 0 && $request['payment_method'] !== "nmi")
                        $voucher_bundle->delete();
                    else {
                        $theatre_json['available_seats'] = array_values(array_diff($theatre_json['available_seats'], [$item['seat']['seat_guid']]));
                        $event->theatre_json = $theatre_json;
                        $event->save();
                    }
                    $vouchers[] = $voucher_bundle;
                }
            }

            if ($total == 0 || $request['payment_method'] === "nmi" || $request['payment_method'] === "nmi_ach") {
                $user->notify(new TicketsReceiptWithQRAttach($user, $vouchers));
                foreach ($request['cart_items'] as $item) {
                    $ticket = Ticket::findOrFail($item['id']);
                    if (isset($item['selected_date'])) {
                        if ($ticket->is_call) {
                            $user->notify(new CallTicketsAvailable($user, $call_voucher));
                            $event->organization->users()->first()->notify(new CallTicketsAvailable($event->organization->users()->first(), $call_voucher));
                        }
                        if ($ticket->is_reservation) {
                            $user->notify(new ReservationTicketsAvailable($user, $reservation_voucher));
                            $event->organization->users()->first()->notify(new ReservationTicketsAvailable($event->organization->users()->first(), $reservation_voucher));
                        }
                    }
                }
                return $this->sendResponse(['cart_items' => $request['cart_items'], 'selected_seats' => $request['selected_seats']], 'success');
            }
            // return $this->sendResponse(['stripe_payment_intent' => $payment_intent], 'success');
            return $this->sendResponse(['charge_id' => $charge_id], 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 402);
        }
    }

    public function paypalTransactionVerify(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);
        try {
            $order = Helper::captureOrder($request->token);
            if ($order && isset($order['status']) && $order['status'] === 'COMPLETED') {
                $vouchers = VoucherBundle::where('charge_id', $request->token)->withTrashed()->get();
                if (count($vouchers) === 0) {
                    return $this->sendError('Transaction is not verified. Please contact by transaction ID: ' . $request->token . '', 422);
                }
                VoucherBundle::onlyTrashed()->where('charge_id', $request->token)->restore();
                $event = Event::findOrFail($vouchers[0]->event_id);
                foreach ($vouchers as $voucher) {
                    if ($voucher->seat_guid) {
                        $theatre_json = $event->theatre_json;
                        $theatre_json['available_seats'] = array_values(array_diff($theatre_json['available_seats'], [$voucher->seat_guid]));
                        $event->theatre_json = $theatre_json;
                        $event->save();
                    } else {
                        if ($voucher->ticket->is_call) {
                            $voucher->user->notify(new CallTicketsAvailable($voucher->user, $voucher));
                            $voucher->ticket->event->organization->users()->first()->notify(new CallTicketsAvailable($voucher->ticket->event->organization->users()->first(), $voucher));
                        }
                        if ($voucher->ticket->is_reservation) {
                            $voucher->user->notify(new ReservationTicketsAvailable($voucher->user, $voucher));
                            $voucher->ticket->event->organization->users()->first()->notify(new ReservationTicketsAvailable($voucher->ticket->event->organization->users()->first(), $voucher));
                        }
                    }
                }
                $vouchers[0]->user->notify(new TicketsReceiptWithQRAttach($vouchers[0]->user, $vouchers));
                return $this->sendResponse($vouchers, 'success');
            }
            if ($order && isset($order['error']) && $order['error']['details'][0]['issue'] === 'ORDER_ALREADY_CAPTURED') {
                $vouchers = VoucherBundle::where('charge_id', $request->token)->withTrashed()->get();
                return $this->sendResponse($vouchers, 'success');
            }
            return $this->sendError('Verification failed!', 422);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    public function paypalSubscriptionTransactionVerify(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'subscription_id' => 'required',
        ]);
        try {
            $order = Helper::captureOrder($request->token);
            if ($order && isset($order['status']) && $order['status'] === 'COMPLETED') {
                $pendingSubscription = PendingSubscription::where('subscription_id', $request['subscription_id'])->withTrashed()->first();
                if ($pendingSubscription && !$pendingSubscription->notified) {
                    $pendingSubscription->notified = 1;
                    $pendingSubscription->save();
                    return $this->sendResponse(["notify" => true], 'success');
                }
                return $this->sendResponse(["notify" => false], 'success');
            }
            return $this->sendError($order, 422);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    public function stripeTransactionVerify(Request $request)
    {
        $request->validate([
            'payment_intent' => 'required'
        ]);
        $stripe = new \Stripe\StripeClient(env('STRIPE_PRIVATE_KEY'));
        try {
            $payment_intent = $stripe->paymentIntents->retrieve($request->payment_intent, []);
            if ($payment_intent && isset($payment_intent['status']) && $payment_intent['status'] === 'succeeded') {
                $vouchers = VoucherBundle::where('charge_id', $request->payment_intent)->withTrashed()->get();
                if (count($vouchers) === 0) {
                    return $this->sendError('Transaction is not verified. Please contact by transaction ID: ' . $request->payment_intent . '', 422);
                }
                VoucherBundle::onlyTrashed()->where('charge_id', $request->payment_intent)->restore();
                $event = Event::findOrFail($vouchers[0]->event_id);
                foreach ($vouchers as $voucher) {
                    if ($voucher->seat_guid) {
                        $theatre_json = $event->theatre_json;
                        $theatre_json['available_seats'] = array_values(array_diff($theatre_json['available_seats'], [$voucher->seat_guid]));
                        $event->theatre_json = $theatre_json;
                        $event->save();
                    } else {
                        if ($voucher->ticket->is_call) {
                            $voucher->user->notify(new CallTicketsAvailable($voucher->user, $voucher));
                            $voucher->ticket->event->organization->users()->first()->notify(new CallTicketsAvailable($voucher->ticket->event->organization->users()->first(), $voucher));
                        }
                        if ($voucher->ticket->is_reservation) {
                            $voucher->user->notify(new ReservationTicketsAvailable($voucher->user, $voucher));
                            $voucher->ticket->event->organization->users()->first()->notify(new ReservationTicketsAvailable($voucher->ticket->event->organization->users()->first(), $voucher));
                        }
                    }
                }
                $vouchers[0]->user->notify(new TicketsAvailable($vouchers[0]->user, $event->organization));
                return $this->sendResponse($vouchers, 'success');
            }
            return response()->json(['data' => $payment_intent], 402);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    public function cartCheckoutPromoApply(Request $request)
    {
        $this->validate($request, [
            'promo_code' => 'required',
            'organization_id' => 'required',
        ]);
        try {
            $promoCode = PromoCode::where('promo_code', $request->promo_code)->where('organization_id', $request->organization_id)->first();
            if ($promoCode) {
                return $this->sendResponse($promoCode->discount_percent, 'success');
            } else {
                return $this->sendError('no promo code', 422);
            }
        } catch (\Throwable $th) {
            return $this->sendError('no promo code', 422);
        }
    }
}
