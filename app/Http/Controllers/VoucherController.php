<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use \PDF;
use App\Models\Event;
use App\Models\MyEvents;
use App\Models\Voucher;
use App\Models\VoucherBundle;
use App\Models\Ticket;
use Illuminate\Http\Request;
use RolfHaug\TokenAuth\LoginToken;

class VoucherController extends Controller
{
    function __construct()
    {
        $this->middleware('auth');
    }

    public function index(MyEvents $myEvents, Request $request)
    {
        $user = auth()->user();
        $myEvents = $myEvents->for($user)->paginate();
        $emailSent = 0;
        if (session('purchase-complete', '0') === '1') {
            $emailSent = 1;
            $request->session()->put('purchase-complete', '0');
        }

        return view('vouchers.index', compact('user', 'myEvents', 'emailSent'));
    }

    public function download($voucher)
    {
        $vouchers = collect(explode(',', $voucher))->map(function ($v) {
            return VoucherBundle::findOrFail($v);
        });

        $ticket = $vouchers[0]->ticket()->withTrashed()->first();

        $seats = [];
        if ($ticket->event->theatre_id && $ticket->event->theatre_json && $vouchers[0]->seat_json && $ticket->is_seat) {
            $seats = $vouchers[0]->seat;
            foreach ($seats as $seat) {
                if (isset($ticket->event->theatre->seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1]['Section'])) {
                    $seat->section = 'Section ' . $ticket->event->theatre->seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1]['Section'];
                }
            }
        }

        $data = [
            'vouchers' => $vouchers,
            'user' => auth()->user(),
            'ticket' => $ticket,
            'seats' => $seats,
        ];

        foreach ($vouchers as $voucher) {
            if (!$voucher->downloaded_at)
                $voucher->update(['downloaded_at' => Carbon::now()]);
        }

        //        return view('pdf.ticket',$data);
        $pdf = PDF::loadView('pdf.ticket', $data);

        $ids = $vouchers->pluck('id')->implode('-');

        return $pdf->setPaper('a4', 'landscape')
            ->setWarnings(false)
            ->download("ticket_{$ids}.pdf");
    }

    public function preview($voucher)
    {
        $vouchers = collect(explode(',', $voucher))->map(function ($v) {
            return auth()->user()->vouchers()->where('refunded_at', null)->findOrFail($v);
        });

        $data = [
            'vouchers' => $vouchers,
            'user' => auth()->user()
        ];

        $user = auth()->user();

        foreach ($vouchers as $voucher) {
            if (!$voucher->downloaded_at)
                $voucher->update(['downloaded_at' => Carbon::now()]);
        }


        $ids = $vouchers->pluck('id')->implode('-');
        $login_token = LoginToken::generate(auth()->user(), true);
        $ticket = $voucher->ticket()->withTrashed()->first();
        $seats = [];
        if ($ticket->event->theatre_id && $ticket->event->theatre_json && $voucher->seat_json && $ticket->is_seat) {
            $seats = $voucher->seat;
            foreach ($seats as $seat) {
                if (isset($ticket->event->theatre->seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1]['Section'])) {
                    $seat->section = 'Section ' . $ticket->event->theatre->seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1]['Section'];
                }
            }
        }
        return view('pdf.ticket', compact('vouchers', 'user', 'login_token', 'ticket', 'seats'));
    }

    public function mapPreview(VoucherBundle $voucher)
    {
        $ticket = $voucher->ticket()->withTrashed()->first();
        return view('pdf.mapPreview', compact('voucher', 'ticket'));
    }

    public function upgrade(Request $request)
    {
        $voucher = Voucher::find($request->voucher_id);

        $ticket = Ticket::findOrFail($voucher->ticket_id);

        $account = $ticket->event->organization->stripe_account;
        $user = $voucher->user();

        if (env('APP_ENV') == 'testing' || env('APP_ENV') == 'local') {
            $account = 'acct_1F0ErgD07ACj1IMu';
        }


        if (!$user) {
            return back()->withErrors([
                'user' => "Sorry, there is something wrong with the ticket user."
            ])
                ->with('alert', 'Whoops!')
                ->with('type', 'danger')
                ->with('message', 'There were some errors.');
        }

        // $voucher->update(['upgrade_charge_id'=>1, 'ticket_type'=>2]);
        try {
            $stripe->calculateUpgradePrice($ticket)
                ->chargeUpgradePrice($account, $user, $request);

            $voucher->update(['upgrade_charge_id' => $charge_id, 'ticket_type' => 2]);
        } catch (\Exception $e) {
            return back()->withErrors(['card' => $e->getMessage()]);
        }

        $request->session()->flash('upgrade-ticket-complete', '1');
        return redirect("/tickets/{$ticket->event->id}");
    }

    public function show($event_id, Request $request)
    {
        $event = Event::where('id', $event_id)->first();
        if (!$event) {
            return redirect('/events');
        }
        $user = auth()->user();
        $event_id = $event->id;
        $vouchers = $user->vouchers()->where('refunded_at', null)->whereHas('ticket', function ($query) use ($event_id) {
            return $query->where('event_id', $event_id)->withTrashed();
        })->orderBy('created_at', 'desc')->get();

        $organization = $event->organization;
        $login_token = LoginToken::generate(auth()->user(), true);

        return view('vouchers.show', compact('user', 'vouchers', 'event', 'organization', 'login_token'));
    }

    public function refund(VoucherBundle $voucher)
    {
        $voucher->refund();

        return redirect()->back();
    }

    public function hideSales(VoucherBundle $voucher)
    {
        $voucher->is_hide_on_sales = 1;
        $voucher->save();
        return redirect()->back();
    }

    public function showSales(VoucherBundle $voucher)
    {
        $voucher->is_hide_on_sales = 0;
        $voucher->save();
        return redirect()->back();
    }
}
