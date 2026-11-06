<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\PromoCode;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $ticket = Ticket::findOrFail($request->ticket_id);
        $cartItems = \Cart::getContent();
        $alreadyAdded = false;
        foreach ($cartItems as $item) {
            if ($item->id === $ticket->id) {
                \Cart::update(
                    $ticket->id,
                    [
                        'quantity' => [
                            'value' => 1,
                            'relative' => true,
                        ],
                    ]
                );
                $alreadyAdded = true;
            }
        }
        $price = $ticket->origin_price;
        // check login user is a subscriber or not
        if (auth()->user() && User::subscribed($ticket->event->organization) && $ticket->discount_percent > 0) {
            $price = (100 - $ticket->discount_percent) * $price;
        }
        if (!$alreadyAdded) {
            \Cart::add([
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => $price,
                'quantity' => 1,
                'attributes' => array(
                    'image' => $ticket->event->image,
                    'currency' => $ticket->event->organization->currencySymbol,
                    'timestamp' => time(),
                    'event' => $ticket->event->name,
                    'event_link' => $ticket->event->link('events.show'),
                    'statement' => $ticket->event->statement,
                    'prompt' => $ticket->event->prompt
                )
            ]);
        }
        return $this->sendResponse([
            'items' => array_values(\Cart::getContent()->toArray()),
            'quantity' => \Cart::getTotalQuantity()
        ], 'Ticekt is added to the cart successfully');
    }

    public function updateCart(Request $request)
    {
        if ($request->quantity <= 0) {
            \Cart::remove($request->id);
        } else \Cart::update(
            $request->id,
            [
                'quantity' => [
                    'relative' => false,
                    'value' => $request->quantity
                ],
            ]
        );
        return $this->sendResponse([
            'items' => array_values(\Cart::getContent()->toArray()),
            'quantity' => \Cart::getTotalQuantity()
        ], 'Ticekt is added to the cart successfully');
    }

    public function removeCart(Request $request)
    {
        try {
            \Cart::remove($request->id);
            return $this->sendResponse([
                'items' => array_values(\Cart::getContent()->toArray()),
                'quantity' => \Cart::getTotalQuantity()
            ], 'success remove!');
        } catch (\Throwable $th) {
            return $this->sendError([
                'items' => array_values(\Cart::getContent()->toArray()),
                'quantity' => \Cart::getTotalQuantity()
            ], $th->getMessage());
        }
    }

    public function clearAllCart()
    {
        \Cart::clear();
        return $this->sendResponse([
            'quantity' => 0,
            'items' => [],
        ], 'success clear cart!');
    }

    public function cartCheckout()
    {
        $cartItems = \Cart::getContent();
        $totalPrice = \Cart::getSubTotal();
        $user = auth()->user();
        return view('checkouts.cartCheckout', compact('cartItems', 'totalPrice', 'user'));
    }

    public function cartCheckoutPromoApply(Request $request){
        $this->validate($request, [
            'promo_code' => 'required'
        ]);
        $promo_code = $request->promo_code;
        $promoCode = null;
        if ($promo_code) {
            $promoCode = PromoCode::where('promo_code', $promo_code)->first();
        }
        $cartItems = \Cart::getContent();
        if ($promoCode) {
            foreach ($cartItems as $item) {
                $ticket = Ticket::findOrFail($item->id);
                \Cart::clearItemConditions($item->id);
                if ($ticket->event->organization->id === $promoCode->organization_id && $promoCode->discount_percent) {
                    $discount = new \Darryldecode\Cart\CartCondition(array(
                        'name' => $promoCode->promo_code,
                        'type' => 'discount',
                        'value' => '-' . $promoCode->discount_percent . '%',
                    ));
                    \Cart::addItemCondition($item->id, $discount);
                }
            }
            return $this->sendResponse(\Cart::getContent()->toArray(), 'success');
        }
        else {
            return $this->sendError('fail', 422);
        }
    }
}
