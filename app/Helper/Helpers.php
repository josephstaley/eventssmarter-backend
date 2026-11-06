<?php

namespace App\Helper;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use RolfHaug\TokenAuth\LoginToken;
use App\Models\PendingSubscription;

class Helpers
{

    static function getPartnerRefferalLink($ticket_id)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->post(env('PAYPAL_API_URL') . '/customer/partner-referrals', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'PayPal-Partner-Attribution-Id' => env('PAYPAL_BN_CODE')
                ],
                'body' => '{
                    "operations": [
                        {
                            "operation": "API_INTEGRATION",
                            "api_integration_preference": {
                                "rest_api_integration": {
                                    "integration_method": "PAYPAL",
                                    "integration_type": "THIRD_PARTY",
                                    "third_party_details": {
                                        "features": [
                                            "PAYMENT",
                                            "REFUND"
                                        ]
                                    }
                                }
                            }
                        }
                    ],
                    "products": [
                        "EXPRESS_CHECKOUT"
                    ],
                    "legal_consents": [
                        {
                            "type": "SHARE_DATA_CONSENT",
                            "granted": true
                        }
                    ],
                    "partner_config_override": {
                        "return_url" : "' . env("APP_URL") . '/success-paypal-connect/' . $ticket_id . '"
                    }
                }'
            ]);
            return \json_decode($res->getBody()->getContents())->links[1]->href;
        } catch (\Throwable $th) {
            return '';
        }
    }

    static function createOrder($ticket, $amount, $voucher_ids)
    {
        $amount = $amount / 100;
        $tax = number_format(.06 * $amount, 2, '.', '');
        $platform_fee = number_format($ticket->event->organization->app_fee_percentage * .01 * $amount, 2, '.', '');

        if ($ticket->has_affiliate && $ticket->affiliate_email && $ticket->affiliate_amount && $ticket->affiliate_paypal_merchant_id) {
            $platform_fee += $ticket->affiliate_amount;
        }

        $item_amount = $amount / count($voucher_ids);

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
        $items = [];
        array_push($items, [
            'name' => $ticket->id,
            'unit_amount' => [
                'currency_code' => $ticket->event->organization->currency,
                'value' => $item_amount
            ],
            'quantity' => count($voucher_ids)
        ]);
        $data = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => $ticket->event->organization->currency,
                        "value" => $amount + $tax,
                        "breakdown" => [
                            "item_total" => [
                                "currency_code" => $ticket->event->organization->currency,
                                "value" => $amount,
                            ],
                            "tax_total" => [
                                "currency_code" => $ticket->event->organization->currency,
                                "value" => $tax
                            ]
                        ]
                    ],
                    "payee" => [
                        "merchant_id" => $ticket->event->organization->paypal_merchant_id
                    ],
                    "payment_instruction" => [
                        "disbursement_mode" => "INSTANT",
                        "platform_fees" => [
                            [
                                "amount" => [
                                    "currency_code" => $ticket->event->organization->currency,
                                    "value" => $platform_fee
                                ]
                            ]
                        ]
                    ],
                    'items' => $items
                ]
            ],
            'application_context' => [
                'return_url' => env('APP_URL') . '/tickets/' . $ticket->event->id . "?" . LoginToken::generate(auth()->user(), true),
                'cancel_url' => env('APP_URL') . '/events'
            ]
        ];
        $order = $provider->createOrder($data);
        return $order;
    }

    static function captureOrder($order_id)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        return $provider->capturePaymentOrder($order_id);
    }

    static function createPayout($amount, $receiver_paypal_id, $event_name)
    {
        $amount = $amount / 100;
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $data = [
            "sender_batch_header" => [
                "email_subject" => "You have a payout from " . $event_name,
                "email_message" => "You have received a payout! Thanks for using " . env("APP_NAME") . "!"
            ],
            "items" => [
                [
                    "recipient_type" => "PAYPAL_ID",
                    "amount" => [
                        "value" => $amount,
                        "currency" => "USD"
                    ],
                    "receiver" => $receiver_paypal_id
                ]
            ]
        ];
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->post(env('PAYPAL_API_v1_URL') . '/payments/payouts', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'PayPal-Partner-Attribution-Id' => env('PAYPAL_BN_CODE')
                ],
                'body' => json_encode($data, true)
            ]);
            return \json_decode($res->getBody()->getContents());
        } catch (\Throwable $th) {
            return '';
        }
    }

    static function createProductOnPaypal($name, $description)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
        $data =  json_decode('{
            "name": "' . $name . '",
            "description": "' . $description . '",
            "type": "SERVICE",
            "category": "SOFTWARE"
          }', true);

        $request_id = 'create-product-' . time();

        $product = $provider->createProduct($data, $request_id);
        return $product['id'];
    }

    static function createMonthlyPlanOnPaypal($product_id, $name, $description, $amount, $currency)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
        $data = json_decode('{
            "product_id": "' . $product_id . '",
            "name": "' . $name . '",
            "description": "' . $description . '",
            "status": "ACTIVE",
            "billing_cycles": [
              {
                "frequency": {
                  "interval_unit": "MONTH",
                  "interval_count": 1
                },
                "tenure_type": "TRIAL",
                "sequence": 1,
                "total_cycles": 1,
                "pricing_scheme": {
                  "fixed_price": {
                    "value": "' . $amount . '",
                    "currency_code": "' . $currency . '"
                  }
                }
              },
              {
                "frequency": {
                  "interval_unit": "MONTH",
                  "interval_count": 1
                },
                "tenure_type": "REGULAR",
                "sequence": 2,
                "total_cycles": 0,
                "pricing_scheme": {
                  "fixed_price": {
                    "value": "' . $amount . '",
                    "currency_code": "' . $currency . '"
                  }
                }
              }
            ],
            "payment_preferences": {
              "auto_bill_outstanding": true,
              "setup_fee_failure_action": "CONTINUE",
              "payment_failure_threshold": 1
            },
            "taxes": {
              "percentage": "6",
              "inclusive": false
            }
          }', true);
        $plan = $provider->createPlan($data, 'create-monthly-subscription-' . time());
        return $plan['id'];
    }

    static function createYearlyPlanOnPaypal($product_id, $name, $description, $amount, $currency)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
        $data = json_decode('{
            "product_id": "' . $product_id . '",
            "name": "' . $name . '",
            "description": "' . $description . '",
            "status": "ACTIVE",
            "billing_cycles": [
              {
                "frequency": {
                  "interval_unit": "MONTH",
                  "interval_count": 1
                },
                "tenure_type": "TRIAL",
                "sequence": 1,
                "total_cycles": 1,
                "pricing_scheme": {
                  "fixed_price": {
                    "value": "' . $amount . '",
                    "currency_code": "' . $currency . '"
                  }
                }
              },
              {
                "frequency": {
                  "interval_unit": "YEAR",
                  "interval_count": 1
                },
                "tenure_type": "REGULAR",
                "sequence": 2,
                "total_cycles": 0,
                "pricing_scheme": {
                  "fixed_price": {
                    "value": "' . $amount . '",
                    "currency_code": "' . $currency . '"
                  }
                }
              }
            ],
            "payment_preferences": {
              "auto_bill_outstanding": true,
              "setup_fee_failure_action": "CONTINUE",
              "payment_failure_threshold": 1
            },
            "taxes": {
              "percentage": "6",
              "inclusive": false
            }
          }', true);
        $plan = $provider->createPlan($data, 'create-yearly-subscription-' . time());
        return $plan['id'];
    }

    static function deactivatePlanOnPaypal($plan_id)
    {
        if ($plan_id) {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);
            $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
            $provider->deactivatePlan($plan_id);
        }
    }

    static function createSubscriptionOnPaypal($plan_id, $back_link)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
        $data = json_decode('{
                "plan_id": "' . $plan_id . '",
                "subscriber": {
                    "name": {
                    "given_name": "' . auth()->user()->fname . '",
                    "surname": "' . (auth()->user()->lname ? auth()->user()->lname : auth()->user()->name) . '"
                    },
                    "email_address": "' . auth()->user()->email . '"
                },
                "application_context": {
                    "brand_name": "' . env('APP_NAME') . '",
                    "locale": "en-US",
                    "shipping_preference": "SET_PROVIDED_ADDRESS",
                    "user_action": "SUBSCRIBE_NOW",
                    "payment_method": {
                    "payer_selected": "PAYPAL",
                    "payee_preferred": "IMMEDIATE_PAYMENT_REQUIRED"
                    },
                    "return_url": "' . $back_link . '",
                    "cancel_url": "' . $back_link . '"
                }
            }', true);
        $subscription = $provider->createSubscription($data);
        PendingSubscription::create([
            'user_id' => auth()->user()->id,
            'subscription_id' => $subscription['id']
        ]);
        return $subscription['links'][0]['href'];
    }

    static function cancelSubscriptionOnPaypal($subscription_id)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $provider->setRequestHeader('PayPal-Partner-Attribution-Id', env('PAYPAL_BN_CODE'));
        $provider->cancelSubscription($subscription_id, 'User Unsubscribed');
    }

    static function verifyPaypalHook($auth_algo, $cert_url, $transmission_id, $transmission_sig, $transmission_time, $webhook_event)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $data = [
            'auth_algo' => $auth_algo,
            'cert_url' => $cert_url,
            'transmission_id' => $transmission_id,
            'transmission_sig' => $transmission_sig,
            'transmission_time' => $transmission_time,
            'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
            'webhook_event' => $webhook_event
        ];
        $client = new \GuzzleHttp\Client();
        try {
            $res = $client->post(env('PAYPAL_API_v1_URL') . '/notifications/verify-webhook-signature', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'PayPal-Partner-Attribution-Id' => env('PAYPAL_BN_CODE')
                ],
                'body' => json_encode($data, true)
            ]);
            return \json_decode($res->getBody()->getContents());
        } catch (\Throwable $th) {
            return false;
        }
    }
}
