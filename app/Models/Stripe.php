<?php

namespace App\Models;

use Stripe\Charge;
use Stripe\Customer;
use Stripe\ApplicationFee;
use Stripe\OAuth;
use Stripe\Refund;
use Stripe\Token;
use Stripe\Transfer;
use Stripe\PaymentIntent;

/**
 *
 * Account Fees for AJE
 *
 * At the time of writing; 15% should go to the App Creator
 *
 * Stripe Fees are 2.9% + 30c
 *
 * At the time of writing; the buy button should include these costs
 *
 * Class Stripe
 * @package App
 */
class Stripe
{
    static $app_fee = 1.10;
    static $app_fee_percentage = 7;

    static function setToken()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_PRIVATE_KEY'));
    }

    function __construct()
    {
        self::setToken();
    }

    public static function testToken()
    {
        self::setToken();
        return \Stripe\Token::create(array(
            "card" => array(
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => 2042,
                "cvc" => "314"
            )
        ));
    }

    public function requestToken($code)
    {
        return OAuth::token([
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);
    }

    /*
     * Money - Pink Floyd
     * https://open.spotify.com/artist/0k17h0D3J5VfsdmQ1iZtE9
     */
    public function getPaymentMethods($user)
    {
        if (!$user->stripe_id) {
            return collect();
        }

        return cache()->remember('pm_' . $user->stripe_id, 5, function () use ($user) {

            try {
                $customer = Customer::retrieve($user->stripe_id);
            } catch (\Exception $e) {
                // Error fetching customer, just abort and don't return cards
                return collect();
            }

            return collect($customer->sources->all()->data)
                ->map(function ($card) {
                    return (object)[
                        'digits' => $card['last4'],
                        'month' => $card['exp_month'],
                        'year' => $card['exp_month'],
                        'id' => $card['id'],
                        'type' => $card['brand'],
                        'title' => $card['brand'] . ' ending in ' . $card['last4'] . ' (Expires: ' . $card['exp_month'] . '/' . $card['exp_year'] . ')'
                    ];
                });
        });
    }

    public static function customer($user, $id)
    {
        if ($user->stripe_id) {
            return Customer::retrieve($user->stripe_id);
        }

        // its a string if we're using the select box
        // and reusing a customer credit card
        // its not a string but an id if we used the JS plugin
        // to create a new card
        if (!is_string($id)) {
            $id = $id->id;
        }

        $stripe_customer = Customer::create(array(
            'email' => $user->email,
            'source' => $id
        ));

        $user->stripe_id = $stripe_customer->id;
        $user->save();

        return $stripe_customer;
    }

    static function check($charge_id)
    {
        self::setToken();

        $charge = Charge::retrieve($charge_id);

        $charge->application_fee = ApplicationFee::retrieve($charge->application_fee);

        return $charge;
    }

    static function refund($payment_intent, $amount)
    {
        self::setToken();

        $transfers = Transfer::all(['transfer_group' => 'group_'.$payment_intent]);
        foreach($transfers->data as $transfer){
            Transfer::createReversal($transfer->id);
        }
        return Refund::create([
            'payment_intent' => $payment_intent,
            'amount' => $amount,
            'refund_application_fee' => false
        ]);
    }

    public function chargeFee($account, $amount, $fee, $customer)
    {
        if($this->isAffiliateAble()){
            $affiliate_account = $this->ticket->affiliate_stripe_account;
            $affiliate_amount = $this->ticket->affiliate_amount * $this->quantity * 100;

            $payload = [
                'customer' => $customer->id,
                'amount' => $affiliate_amount,
                'currency' => 'usd',
                'application_fee' => 0, // how much does athletic junction siphon off for handling
                'destination' => [
                    'account' => $affiliate_account // where do we ultimately send this money?
                ]
            ];
            $affiliate_charge = \Stripe\Charge::create($payload);
            $this->affiliate_transfer_amount = $affiliate_amount;
            $this->affiliate_transfer_id = $affiliate_charge->id;
        } else {
            $this->affiliate_transfer_amount = 0;
            $this->affiliate_transfer_id = 0;
        }

        $amount_for_organization = $amount - $this->affiliate_transfer_amount;
        $payload = [
            'customer' => $customer->id,
            'amount' => $amount_for_organization,
            'currency' => 'usd',
            'application_fee' => $fee, // how much does athletic junction siphon off for handling
            'destination' => [
                'account' => $account // where do we ultimately send this money?
            ]
        ];
        $org_charge = \Stripe\Charge::create($payload);

        return $org_charge;
    }

    public function isAffiliateAble() {
        $ticket = $this->ticket;
        if(!$ticket) return false;

        if($ticket->has_affiliate && $ticket->affiliate_email && $ticket->affiliate_amount && $ticket->affiliate_stripe_account)
            return true;

        return false;
    }

    public function transfer($account, $amount) {
        $payload = [
            'amount' => $amount,
            'currency' => 'usd',
            'destination' => $account,
            'transfer_group' =>'order123'
        ];

        return \Stripe\Transfer::create($payload);
    }

    public function calculate(Ticket $ticket, $type, $quantity)
    {
        $this->ticket = $ticket;
        $this->quantity = $quantity;
        $this->type = $type;

        $this->amount = $type == 1 ? $ticket->getPriceInCents() * $quantity : $ticket->getVipPriceInCents() * $quantity;
        $this->fee = $type == 1 ? $ticket->getFee() * $quantity:  $ticket->getVipFee() * $quantity;

        return $this;
    }

    function getPriceFee($cents, $app_fee_percentage)
    {
        // $stripe_fee = $cents * StripeFees::$actual_percentage + StripeFees::$fixed;
        $michael_fee = $cents * (.01 * $app_fee_percentage) + 10; // (5% + 10 cents + stripe fee : 2.9% + 30 cents   all: 7.9% + 40 cents )
        $this->fee = intval(ceil($michael_fee));
        return $this->fee;
    }

    public function calculateWithBundles(Ticket $ticket, $bundles, $app_fee_percentage)
    {
        $this->ticket = $ticket;


        $price = 0;
        $quantity = 0;

        foreach($bundles as $bundle) {
            $quantity += $bundle['count'] * $bundle['purchase_quantity'];
            $price += $bundle['price'] * $bundle['purchase_quantity'];
        }
        $this->quantity = $quantity;
        $this->amount = $price;
        $this->fee = $this->getPriceFee($price, $app_fee_percentage);

        return $this;
    }

    public function calculateUpgradePrice(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->quantity = 1;
        $this->type = 2;

        $this->amount = $ticket->getVipPriceInCents() - $ticket->getPriceInCents();
        $this->fee = $ticket->getUpgradeFee();

        return $this;
    }

    public function chargeUpgradePrice($account, $user, $request)
    {
        $charge = $this->chargeUpgradeFee($account, $this->amount, $this->fee, Stripe::customer($user, json_decode($request->stripe_token)));

        return $charge;
    }

    public function chargeUpgradeFee($account, $amount, $fee, $customer)
    {
        $payload = [
            'customer' => $customer->id,
            'amount' => $amount,
            'currency' => 'usd',
            'application_fee' => $fee, // how much does athletic junction siphon off for handling
            'destination' => [
                'account' => $account // where do we ultimately send this money?
            ]
        ];
        $org_charge = \Stripe\Charge::create($payload);

        return $org_charge;
    }

    public static function createBankAccountToken($name, $bank, $holder_type){
        self::setToken();
        $array = [
            'country' => $bank['bank_country'],
            'currency' => $bank['currency'],
            'account_holder_name' => $name,
            'account_holder_type' => $holder_type,
            'account_number' => $bank['bank_account_number']
        ];
        if (isset($bank['bank_routing_number'])){
            $array['routing_number'] = $bank['bank_routing_number'];
        }
        return \Stripe\Token::create(array(
            'bank_account' => $array
        ));
    }
}
