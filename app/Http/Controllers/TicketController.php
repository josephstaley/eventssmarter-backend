<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Helpers;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Models\VoucherBundle;
use App\Classes\AgoraDynamicKey\RtcTokenBuilder;

class TicketController extends Controller
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
     * @param $organization
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function create($organization, $title, Event $event)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");

        $ticket = new Ticket;

        return view('tickets.create', compact('organization', 'event', 'ticket'));
    }


    public function edit($organization, $id)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");

        $ticket = Ticket::where('id', '=', $id)->first();

        $event = $ticket->event;

        return view('tickets.edit', compact('ticket', 'organization', 'event'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $organization, $title, Event $event)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");

        $fields = $this->validate($request, [
            'name' => 'required|min:3',
            'description' => '',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'bundle_counts' => 'nullable|array',
            'bundle_prices' => 'nullable|array',
            'bundle_names' => 'nullable|array',
            'is_live_stream' => 'required|numeric',
            'is_private' => 'required|numeric',
            'stream_url' => 'nullable|string',
            'is_donation' => 'required',
            'is_call' => 'required',
            'is_reservation' => 'required',
            'is_seat' => 'required',
            'is_subscribe' => 'required',
            'discount_percent' => 'nullable|numeric'
        ], []);

        if ($request['is_donation']) {
            $bundle_counts = [1];
            $bundle_prices = [0];
            $bundle_names = ['Donate Ticket'];
            $fields['price'] = 0;
        } else {
            $bundle_counts = $fields['bundle_counts'];
            $bundle_prices = $fields['bundle_prices'];
            $bundle_names = $fields['bundle_names'];
            foreach ($bundle_names as $item) {
                if (!$item) {
                    $request->session()->flash('bundle-name-error', 1);
                    return redirect()->back();
                }
            }
        }

        unset($fields['bundle_counts']);
        unset($fields['bundle_prices']);
        unset($fields['bundle_names']);

        if (!$fields['stream_url'])
            $fields['stream_url'] = '';

        $fields['slug'] = str_slug($fields['name']);

        $ticket = $event->tickets()->create($fields);

        if ($event->theatre_id && $event->theatre_json && $request['is_seat']) {
            $seat_json = $event->theatre_json;
            if ($event->theatre->svg_path) {
                $selectedSections = \json_decode($request['selectedSections']);
                foreach ($selectedSections as $section) {
                    if ($section && isset($section->sectionRows))
                        foreach ($section->sectionRows as $i => $row) {
                            $new_row = $seat_json['colAreas']['objArea'][$section->sectionNum - 1]['objRow'][$i];
                            $new_row['rowStatus'] = 1;
                            $new_row['ticket_id'] = $ticket->id;
                            $new_row['price'] = $ticket->getOriginal()['price'];
                            foreach ($new_row['objSeat'] as $seat) {
                                $new_row['objSeat'][$seat['seatNumber'] - 1]['SeatStatus'] = "1";
                                $new_row['objSeat'][$seat['seatNumber'] - 1]['ticket_id'] = $ticket->id;
                                $new_row['objSeat'][$seat['seatNumber'] - 1]['price'] = $ticket->getOriginal()['price'];
                            }
                            $seat_json['colAreas']['objArea'][$section->sectionNum - 1]['objRow'][$i] = $new_row;
                        }
                }
            } else {
                $selectedSeats = \json_decode($request['selectedSeats']);
                foreach ($selectedSeats as $seat) {
                    $_seat = $seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1];
                    $_seat['SeatStatus'] = "1";
                    $_seat['ticket_id'] = $ticket->id;
                    $_seat['price'] = $ticket->getOriginal()['price'];
                    $seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1] = $_seat;
                }
            }
            $event->theatre_json = $seat_json;
            $event->save();
        }

        if (request('is_call') == "1") {
            $this->validate($request, [
                'call_available_start_time' => 'required',
                'call_available_end_time' => 'required',
                'call_available_weekends' => 'required'
            ], []);
            $ticket->update([
                'call_available_start_time' => request('call_available_start_time'),
                'call_available_end_time' => request('call_available_end_time'),
                'call_available_weekends' => request('call_available_weekends'),
                'call_time' => request('call_time')
            ]);
        }

        if (request('is_reservation') == "1") {
            $this->validate($request, [
                'reservation_available_start_time' => 'required',
                'reservation_available_end_time' => 'required',
                'reservation_available_weekends' => 'required'
            ], []);
            $ticket->update([
                'reservation_available_start_time' => request('reservation_available_start_time'),
                'reservation_available_end_time' => request('reservation_available_end_time'),
                'reservation_available_weekends' => request('reservation_available_weekends'),
                'reservation_time' => request('reservation_time')
            ]);
        }

        if ($fields['is_live_stream'] && $request['stream_type'] === "0") {
            //live stream mode and stream type is eventssmarter live stream
            $ticket->update([
                'stream_url' => 'https://live.eventssmarter.com/live/' . $ticket->id
            ]);
        }

        if ($ticket) {
            for ($i = 0; $i < count($bundle_counts); $i++) {
                $bundle = [
                    'count' => $bundle_counts[$i],
                    'price' => $bundle_prices[$i],
                    'bundle_name' => $bundle_names[$i],
                ];
                $ticket->bundles()->create($bundle);
            }
        }

        $request->session()->flash('ticket-create', '1');
        return redirect($event->link('events.show', ['show_code' => true]));
    }


    /**
     * @param Request $request
     * @param $organization
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $organization, $id)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        $this->validate($request, [
            'name' => 'required|min:3',
            'description' => '',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'bundle_counts' => 'nullable|array',
            'bundle_prices' => 'nullable|array',
            'bundle_names' => 'required|array',
            'is_live_stream' => 'required|numeric',
            'is_private' => 'required|numeric',
            'stream_url' => 'nullable|string',
            'is_donation' => 'required',
            'is_call' => 'required',
            'is_reservation' => 'required',
            'is_seat' => 'required',
            'is_subscribe' => 'required',
            'discount_percent' => 'nullable|numeric',
        ], []);

        if ($request['is_donation']) {
            $bundle_counts = [1];
            $bundle_prices = [0];
            $bundle_names = ['Donate Ticket'];
        } else {
            $bundle_counts = request('bundle_counts');
            $bundle_prices = request('bundle_prices');
            $bundle_names = request('bundle_names');
            foreach ($bundle_names as $item) {
                if (!$item) {
                    $request->session()->flash('bundle-name-error', 1);
                    return redirect()->back();
                }
            }
        }

        $ticket = Ticket::where('id', '=', $id)->first();

        abort_unless(Helpers::can('update', $ticket), 403, "You don't have permission to view this page.");

        $ticket->bundles()->delete();
        for ($i = 0; $i < count($bundle_counts); $i++) {
            $bundle = [
                'count' => $bundle_counts[$i],
                'price' => $bundle_prices[$i],
                'bundle_name' => $bundle_names[$i],
            ];
            $ticket->bundles()->create($bundle);
        }

        if ($ticket->affiliate_amount) {
            $affiliate_amount = request('affiliate_amount') ? intval(request('affiliate_amount') * 100) : $ticket->affiliate_amount;
        }

        if ($ticket->is_seat && !request('is_seat')) {
            $seat_json = $ticket->event->theatre_json;
            foreach ($seat_json['colAreas']['objArea'] as $objAreaIndex => $objArea) {
                foreach ($objArea['objRow'] as $objRowIndex => $objRow) {
                    foreach ($objRow['objSeat'] as $objSeatIndex => $objSeat) {
                        if ($objSeat['SeatStatus'] === "1" && $objSeat['ticket_id'] === $ticket->id && $objSeat['price']) {
                            $objSeat['SeatStatus'] = "0";
                            unset($objSeat['price']);
                            unset($objSeat['ticket_id']);
                            $seat_json['colAreas']['objArea'][$objAreaIndex]['objRow'][$objRowIndex]['objSeat'][$objSeatIndex] = $objSeat;
                        }
                    }
                }
            }
            $ticket->event->theatre_json = $seat_json;
            $ticket->event->save();
        }

        $ticket->update([
            'name' => request('name'),
            'slug' => str_slug(request('name')),
            'description' => request('description'),
            'quantity' => request('quantity'),
            'price' => $request['is_donation'] ? 0 : intval(request('price')),
            'is_live_stream' => request('is_live_stream'),
            'is_private' => request('is_private'),
            'stream_url' => request('stream_url'),
            'is_donation' => request('is_donation'),
            'is_call' => request('is_call'),
            'is_reservation' => request('is_reservation'),
            'is_seat' => request('is_seat'),
            'is_subscribe' => request('is_subscribe'),
            'discount_percent' => request('discount_percent'),
        ]);

        if (request('is_call') == "1") {
            $this->validate($request, [
                'call_available_start_time' => 'required',
                'call_available_end_time' => 'required',
                'call_available_weekends' => 'required'
            ], []);
            $ticket->update([
                'call_available_start_time' => request('call_available_start_time'),
                'call_available_end_time' => request('call_available_end_time'),
                'call_available_weekends' => request('call_available_weekends'),
                'call_time' => request('call_time')
            ]);
        }

        if (request('is_reservation') == "1") {
            $this->validate($request, [
                'reservation_available_start_time' => 'required',
                'reservation_available_end_time' => 'required',
                'reservation_available_weekends' => 'required'
            ], []);
            $ticket->update([
                'reservation_available_start_time' => request('reservation_available_start_time'),
                'reservation_available_end_time' => request('reservation_available_end_time'),
                'reservation_available_weekends' => request('reservation_available_weekends'),
                'reservation_time' => request('reservation_time')
            ]);
        }

        if (request('is_live_stream') && $request['stream_type'] === "0") {
            //live stream mode and stream type is eventssmarter live stream
            $ticket->update([
                'stream_url' => 'https://live.eventssmarter.com/live/' . $ticket->id
            ]);
        }

        if ($ticket->event->theatre_id && $ticket->event->theatre_json && $request['is_seat']) {
            $seat_json = $ticket->event->theatre_json;
            if ($ticket->event->theatre->svg_path) {
                foreach ($seat_json['colAreas']['objArea'] as $section) {
                    foreach ($section['objRow'] as $row) {
                        if (isset($row['rowStatus']) && $row['rowStatus'] && $row['ticket_id'] === $ticket->id) {
                            $row = $seat_json['colAreas']['objArea'][$section['AreaNum'] - 1]['objRow'][$row['GridRowId'] - 1];
                            unset($row['rowStatus']);
                            unset($row['ticket_id']);
                            unset($row['price']);
                            $seat_json['colAreas']['objArea'][$section['AreaNum'] - 1]['objRow'][$row['GridRowId'] - 1] = $row;
                        }
                    }
                }
                $selectedSections = \json_decode($request['selectedSections']);
                foreach ($selectedSections as $section) {
                    if ($section && isset($section->sectionRows)) {
                        foreach ($section->sectionRows as $i => $row) {
                            $new_row = $seat_json['colAreas']['objArea'][$section->sectionNum - 1]['objRow'][$i];
                            $new_row['rowStatus'] = 1;
                            $new_row['ticket_id'] = $ticket->id;
                            $new_row['price'] = $ticket->getOriginal()['price'];
                            foreach ($new_row['objSeat'] as $seat) {
                                $new_row['objSeat'][$seat['seatNumber'] - 1]['SeatStatus'] = "1";
                                $new_row['objSeat'][$seat['seatNumber'] - 1]['ticket_id'] = $ticket->id;
                                $new_row['objSeat'][$seat['seatNumber'] - 1]['price'] = $ticket->getOriginal()['price'];
                            }
                            $seat_json['colAreas']['objArea'][$section->sectionNum - 1]['objRow'][$i] = $new_row;
                        }
                    }
                }
            } else if ($request['addedSeats'] || $request['removedSeats']) {
                $addedSeats = \json_decode($request['addedSeats']);
                $removedSeats = \json_decode($request['removedSeats']);
                if ($removedSeats && count($removedSeats) > 0) {
                    foreach ($removedSeats as $seat) {
                        $_seat = $seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1];
                        $_seat['SeatStatus'] = "0";
                        unset($_seat['ticket_id']);
                        unset($_seat['price']);
                        $seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1] = $_seat;
                    }
                }
                if ($addedSeats && count($addedSeats) > 0) {
                    foreach ($addedSeats as $seat) {
                        $_seat = $seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1];
                        $_seat['SeatStatus'] = "1";
                        $_seat['ticket_id'] = $ticket->id;
                        $_seat['price'] = $ticket->getOriginal()['price'];
                        $seat_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1] = $_seat;
                    }
                }
            }
            $ticket->event->theatre_json = $seat_json;
            $ticket->event->save();
        }

        $request->session()->flash('ticket-update', '1');
        return redirect($ticket->event->link('events.show'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);

        abort_unless(Helpers::can('update', $ticket), 403, "You don't have permission to view this page.");

        $ticket->delete();

        return redirect($ticket->event->link('events.show'));
    }

    public function stream($organization, $id)
    {
        $userId = auth()->user()->id;
        $organization = Organization::where('slug', $organization)->firstOrFail();;
        //$organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();
        $vouchers = VoucherBundle::where(['ticket_id' => $id, 'user_id' => $userId])->groupBy('user_id')->get();
        $ticket = Ticket::where('id', '=', $id)->first();
        if (!$vouchers->count()) {
            return redirect()->route('checkout.create', [
                'type' => 'user',
                'id' => $id,
                'quantity' => 0,
            ]);
        }
        $ticket = Ticket::where('id', '=', $id)->withTrashed()->first();
        $id = $ticket->event_id;
        $orgId = $organization->id;
        $orgBanner = Banner::where('link_id', $orgId)->where('link_type', '1')->get();
        $model = Banner::where('link_id', $id)->where('link_type', '0')->get();
        $banner = array();
        foreach ($orgBanner as $orgItem) {
            array_push($banner, $orgItem);
        }
        foreach ($model as $eventItem) {
            array_push($banner, $eventItem);
        }
        $agora_token = '';
        if (strpos($ticket->stream_url, 'live.eventssmarter.com/live') !== false) {
            $appID = env('AGORA_APP_ID');
            $appCertificate = env('AGORA_APP_CERTIFICATE');
            $channelID = substr($ticket->stream_url, strpos($ticket->stream_url, '/live/') + 6);
            $channelName = "live-" . $channelID;
            $role = RtcTokenBuilder::RoleSubscriber;
            $privilegeExpiredTs = 0;
            $agora_token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, auth()->user()->id, $role, $privilegeExpiredTs);
        }

        return view('tickets.stream', compact('ticket', 'organization', 'banner', 'agora_token'));
    }
}
