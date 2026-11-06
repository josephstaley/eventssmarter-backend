<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EventMember;
use App\Models\Event;
use Carbon\Carbon;
use App\Models\Helpers;

class VoucherAPIController extends Controller
{
    function getVouchers(Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $event_ids = $user->vouchers()
            ->where('refunded_at', null)
            ->with(['ticket' => fn($q) => $q->withTrashed()])
            ->whereHas('ticket.event')
            ->whereHas('ticket.event.organization')
            ->groupBy('ticket_id')
            ->get()->pluck('ticket')->groupBy('event_id')->keys();
        $seat_event_ids = $user->vouchers()
            ->where('refunded_at', null)
            ->whereNotNull('seat_guid')
            ->with(['event', 'event.theatre'])
            ->whereHas('event')
            ->whereHas('event.organization')
            ->get()
            ->groupBy('event_id')->keys();
        $event_ids = $event_ids->merge($seat_event_ids);
        $events = Event::whereIn('id', $event_ids)->with('organization')->orderBy('starts_at')->get()->makeHidden(['theatre_json']);
        $past_events = [];
        $upcoming_events = [];
        foreach ($events as $item) {
            $item->organization->makeHidden(['bank_routing_number', 'bank_account_number', 'app_fee_percentage', 'paypal_merchant_id', 'sales_time_delay', 'bank_address', 'bank_info_first_name', 'bank_info_last_name', 'bank_info_personal_address', 'bank_info_business_email', 'bank_info_swift_code']);
            if (Carbon::now()->greaterThan($item->ends_at)) {
                $past_events[] = $item;
            } else
                $upcoming_events[] = $item;
        }
        $vouchers = $user->vouchers()
            ->where('refunded_at', null)
            ->with(['ticket' => fn($q) => $q->withTrashed()])
            ->whereHas('ticket.event')
            ->whereHas('ticket.event.organization')->orderBy('created_at', 'desc')->get()->makeHidden(['is_hide_on_sales', 'deleted_at', 'ticket_type', 'adsid', 'upgrade_charge_id', 'affiliate_status', 'affiliate_amount']);
        $seat_vouchers = $user->vouchers()
            ->where('refunded_at', null)
            ->whereNotNull('seat_guid')
            ->with(['event', 'event.theatre', 'event.organization'])
            ->whereHas('event')
            ->whereHas('event.organization')
            ->orderBy('created_at', 'desc')->get()->makeHidden(['is_hide_on_sales', 'deleted_at', 'ticket_type', 'adsid', 'upgrade_charge_id', 'affiliate_status', 'affiliate_amount']);
        $vouchers = $vouchers->merge($seat_vouchers)->sortByDesc('created_at')->values()->all();
        foreach ($vouchers as $voucher) {
            if (isset($voucher->ticket) && $voucher->ticket->is_live_stream) {
                $voucher->ticket->makeHidden(['stream_url']);
            }
        }
        return $this->sendResponse(['vouchers' => $vouchers, 'upcoming_events' => $upcoming_events, 'past_events' => $past_events], 'success');
    }

    function getSalesByEvent(Event $event, Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->first();
            if (!$member) {
                return $this->sendError('You have no permission to see this event sales', 422);
            }
        }

        $sales = with(clone $event->sales()->query);
        $search = $request['search'];
        if (isset($search) && $search) {
            $sales = $sales->where(function ($query) use ($search) {
                $query->where('users.name', 'LIKE', '%' . $search . '%')->orWhere('users.email', 'LIKE', '%' . $search . '%');
                if (auth()->user()->admin) {
                    $query->orWhere('charge_id', 'LIKE', '%' . $search . '%');
                }
            });
        }
        $sales = $sales->select(['users.name', 'users.email', 'users.phone_number', 'voucher_bundles.detail_text', 'voucher_bundles.detail_amount', 'voucher_bundles.created_at as purchased_date', 'voucher_bundles.used_at as scanned_date', 'voucher_bundles.charge_id', 'voucher_bundles.affiliate_amount', 'promo_codes.promo_code', 'voucher_bundles.adsid', 'voucher_bundles.prompt_answer_json'])->paginate(8);
        $currency = Helpers::getCurrency($event->organization->currency);
        $sold = $event->sales()->sold;
        $sold_amount = $event->sales()->sold_amount;
        $tickets = $event->sales()->tickets;
        $attendees = $event->sales()->attendees;
        return $this->sendResponse(['sales' => $sales, 'currency' => $currency, 'tickets' => $tickets, 'sold' => $sold, 'attendees' => $attendees, 'sold_amount' => $sold_amount], 'success');

        // $promo_codes = with(clone $event->sales()->query)->select(DB::raw('count(*) as count'), DB::raw('sum(voucher_bundles.affiliate_amount) as sum_amount'), 'promo_code_org_id', 'promo_code', 'promo_codes.affiliate_email')->join('promo_codes', 'promo_codes.id', '=', 'promo_code_org_id')->groupBy('promo_code_org_id')->get();
    }

    public function exportcsv(Event $event)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("You have no permission to download", 401);
            }
        }
        $fileName = $event->name . '_ticket_sales.csv';

        $sales = $event->sales()->query->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        $currency = $event->organization->currency;
        $zero_decimal = Helpers::getCurrency($currency)['zero_decimal'];
        $columns = array('No', 'Name', 'Email', 'Phone', 'Ticket Name', "Price ($currency)", 'Promo Code', 'AdsId', 'Affiliate Amount', 'Address', 'Employer', 'Occupation', 'Prompt');
        $callback = function () use ($sales, $columns, $currency, $zero_decimal) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($sales as $key => $sale) {
                $row['No'] = $key + 1;
                $row['Name'] = $sale->name;
                $row['Email'] = $sale->email;
                $row['Phone'] = $sale->phone_number;
                $row['Ticket Name'] = $sale->detail_text;
                $row["Price ($currency)"] = $zero_decimal ? $sale->detail_amount : $sale->detail_amount / 100;
                $row['Promo Code'] = $sale->promo_code;
                $row['AdsId'] = $sale->adsid;
                $row['Affiliate Amount'] = $sale->affiliate_amount;
                $row['Address'] = $sale->address;
                $row['Employer'] = $sale->employer;
                $row['Occupation'] = $sale->occupation;
                $prompt = '';
                if ($sale->prompt_answer_json) {
                    $prompt_answers = json_decode($sale->prompt_answer_json);
                    foreach ($prompt_answers as $answer) {
                        $prompt .= $answer->title . ': ' . $answer->answer . ', ';
                    }
                    if ($prompt !== '') {
                        $prompt = substr($prompt, 0, -2);
                    }
                }
                $row['Prompt'] = $prompt;
                fputcsv($file, array($row['No'], $row['Name'], $row['Email'], $row['Phone'], $row['Ticket Name'], $row["Price ($currency)"], $row['Promo Code'], $row['AdsId'], $row['Affiliate Amount'], $row['Address'], $row['Employer'], $row['Occupation'], $row['Prompt']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
