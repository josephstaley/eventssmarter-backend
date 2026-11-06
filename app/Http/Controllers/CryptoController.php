<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VoucherBundle;
use Carbon\Carbon;

use App\Notifications\TicketsAvailable;
use App\Notifications\CallTicketsAvailable;
use App\Notifications\ReservationTicketsAvailable;

use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Web3\Contract;

class CryptoController extends Controller
{

    public function paymentVerify(Request $request) {
        $this->validate($request, [
            'voucher_ids' => 'required',
            'hash' => 'required',
            'amount' => 'required',
        ]);
        try {
            $voucher_ids = $request['voucher_ids'];
            $hash = $request['hash'];
            $amount = $request['amount'];
            $contract = new Contract(new HttpProvider(new HttpRequestManager(config('crypto.host'), 10)), config('crypto.abi'));
            $response = '';
            $contract->at(config('crypto.smartcontract_address'))->call('contains', $voucher_ids , function($err, $res) use ($voucher_ids, $hash, $amount, $response){
                if($res[0]){
                    $paymentHandledStatus = true;
                    foreach($voucher_ids as $voucher_id){
                        $voucher_bundle = VoucherBundle::withTrashed()->where('id', $voucher_id)->restore();
                        $voucher_bundle = VoucherBundle::where('id', $voucher_id)->first();

                        if ($voucher_bundle->charge_id !== "0") continue;
                        $voucher_bundle->charge_id = $hash;
                        $voucher_bundle->detail_amount = $amount;
                        $voucher_bundle->save();
                        $paymentHandledStatus = false;
                    }
                    if (!$paymentHandledStatus){
                        $event = $voucher_bundle->ticket->event;
                        $theatre_json = $event->theatre_json;
                        foreach($voucher_ids as $voucher_id){
                            $voucher_bundle = VoucherBundle::where('id', $voucher_id)->first();
                            if ($voucher_bundle && $voucher_bundle->seat_json && $theatre_json && $voucher_bundle->ticket->is_seat){
                                $seats = \json_decode($voucher_bundle->seat_json);
                                if ($seats){
                                    foreach($seats as $seat){
                                        $_seat = $theatre_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1];
                                        $_seat['SeatStatus'] = "1";
                                        $_seat['selled_at'] = Carbon::now();
                                        $_seat['user_id'] = $voucher_bundle->user()->first()->id;
                                        $theatre_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1] = $_seat;
                                    }
                                }
                            }
                        }
                        $event->theatre_json = $theatre_json;
                        $event->save();
                        if ($voucher_bundle && $voucher_bundle->seat_json && $theatre_json && $voucher_bundle->ticket->is_seat){
                            // cancel current seat's payment intent
                            $vouchers_in_purchasing = VoucherBundle::onlyTrashed()->where('ticket_id', $voucher_bundle->ticket->id)
                            ->whereRaw("JSON_CONTAINS(seat_json->'$.AreaNum', '{$seat->AreaNum}')")
                            ->whereRaw("JSON_CONTAINS(seat_json->'$.GridRowId', '{$seat->GridRowId}')")
                            ->whereRaw("JSON_CONTAINS(seat_json->'$.seatNumber', '{$seat->seatNumber}')")
                            ->get();
                            foreach($vouchers_in_purchasing as $voucher_in_purchasing){
                                $stripe->paymentIntents->cancel(
                                    $voucher_in_purchasing->upgrade_charge_id,
                                    []
                                );
                            }
                        }
                        $voucher_bundle->ticket->quantity -= count($voucher_ids);
                        $voucher_bundle->ticket->save();
                        if ($voucher_bundle->ticket->is_call){
                            $voucher_bundle->user()->first()->notify(new CallTicketsAvailable($voucher_bundle->user()->first(), $voucher_bundle));
                            $voucher_bundle->ticket->event->organization->users()->first()->notify(new CallTicketsAvailable($voucher_bundle->ticket->event->organization->users()->first(), $voucher_bundle));
                        }
                        else if ($voucher_bundle->ticket->is_reservation){
                            $voucher_bundle->user()->first()->notify(new ReservationTicketsAvailable($voucher_bundle->user()->first(), $voucher_bundle));
                            $voucher_bundle->ticket->event->organization->users()->first()->notify(new ReservationTicketsAvailable($voucher_bundle->ticket->event->organization->users()->first(), $voucher_bundle));
                        }
                        else {
                            $voucher_bundle->user()->first()->notify(new TicketsAvailable($voucher_bundle->user()->first(), $voucher_bundle->ticket->event->organization));
                        }
                    }
                    echo json_encode([
                        'success' => 1,
                        'message' => 'success',
                    ]);
                }
                else {
                    echo json_encode([
                        'success' => 0,
                        'message' => 'verification failed',
                    ]);
                }
            });
        } catch (\Throwable $th) {

        }
    }
}
