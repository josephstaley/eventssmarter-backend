<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\VoucherBundle;
use App\Models\Banner;
use App\Models\Organization;
use App\Models\EventFeedback;
use App\Models\Theatre;
use App\Models\EventAttachment;
use App\Models\TicketPrompt;
use App\Models\TicketBundle;
use App\Models\EventMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Helpers;
use App\Models\Subscription;

use Illuminate\Support\Facades\Auth;


class EventAPIController extends Controller
{

    function upcoming_events()
    {
        $events = Event::where('ends_at', '>=', Carbon::now())
            ->where('events.is_private', 0)
            ->whereHas('organization')
            ->with([
                'organization' => function ($query) {
                    $query->select('id', 'category', 'name', 'slug');
                }
            ])
            ->with('banners')
            ->orderBy('ends_at')
            ->limit(12)
            ->get();

        return $this->sendResponse($events, 'Upcoming events are successfully retrieved');
    }

    function events(Request $request)
    {
        $events_query = Event::where('ends_at', '>=', Carbon::now())->where('events.is_private', 0);
        if ($request->search_name) {
            $events_query = $events_query->where('events.name', $request->search_name);
        }
        if ($request->search_start_date) {
            $events_query = $events_query->where('events.starts_at', '>=', Carbon::createFromFormat('m/d/Y', $request->search_start_date));
        }
        if ($request->search_end_date) {
            $events_query = $events_query->where('events.ends_at', '<=', Carbon::createFromFormat('m/d/Y', $request->search_end_date));
        }
        if ($request->search_location) {
            $events_query = $events_query->where(function ($q) use ($request) {
                $q->where('events.location_name', 'like', '%' . $request->search_location . '%')
                    ->orWhere('events.address', 'like', '%' . $request->search_location . '%')
                    ->orWhere('events.street', 'like', '%' . $request->search_location . '%')
                    ->orWhere('events.city', 'like', '%' . $request->search_location . '%')
                    ->orWhere('events.state', 'like', '%' . $request->search_location . '%');
            });
        }

        $events = $events_query->with([
            'organization' => function ($query) {
                $query->select('id', 'category', 'name', 'slug');
            }
        ])->with('banners')->orderBy('ends_at')->paginate(12);

        return $this->sendResponse($events, 'Upcoming events are successfully retrieved');
    }


    function show($organization, $title, $eventId, Request $request)
    {
        if (isset($request['token'])) {
            try {
                $event_id = Crypt::decryptString($request['token']);
                Cookie::queue('event_token', $request['token'], 60 * 72);
            } catch (\Throwable $th) {
                return $this->sendError($th->getMessage(), 404);
            }
        }
        if ($request->cookie('event_token')) {
            $event_id = Crypt::decryptString($request->cookie('event_token'));
        }

        try {
            $organization = Organization::where('slug', $organization)->firstOrFail();
            $user = Auth::guard('sanctum')->user();
            if ($organization->is_subscription && $user) {
                $subscription = Subscription::where('user_id', $user->id)->where('organization_id', $organization->id)->first();
                if ($subscription) {
                    $organization->is_subscribed = true;
                }
            }
        } catch (ModelNotFoundException $e) {
            return $this->sendError($e->getMessage(), 404);
        }
        $organization->makeHidden(
            'bank_account_number',
            'bank_routing_number',
            'bank_address',
            'bank_info_business_email',
            'bank_info_first_name',
            'bank_info_last_name',
            'bank_info_personal_address',
            'bank_info_swift_code',
            'bank_routing_number',
            'paypal_merchant_id',
            'sales_time_delay',
            'zelle_email',
            'zelle_phone_number'
        );

        $organization->currency_rate = Helpers::getCurrencyRate($organization->currency);

        $event = Event::with('theatre')->with('attachments')->findOrFail($eventId)->makeHidden('organization');
        if (empty($event)) {
            return $this->sendError("Event not found", 404);
        }

        if ($event->tickets()->count() == 1 && request('checkout')) {
            return redirect()->route('checkout.decide', ['id' => $event->tickets->first()->id, 'quantity' => 1]);
        }

        $event_banners = Banner::where('link_id', $event->id)->where('link_type', '0')->get();
        $banner = array();
        foreach ($event_banners as $eventItem) {
            array_push($banner, $eventItem);
        }

        // get tickets of the event
        $tickets = Ticket::where('event_id', $event->id)->with('bundles')->get()->makeHidden(['event', 'media_url', 'stream_url', 'attendee_url', 'rtmp_key', 'rtmp_uid', 'rtmp_url']);
        $ticket_ids = [];
        foreach ($tickets as $ticket) {
            $ticket_ids[] = $ticket->id;
        }
        // get vouchers ( purchased ones ) of the event
        $purchasers_count = count(VoucherBundle::whereIn('ticket_id', $ticket_ids)->groupBy('user_id')->select('user_id', DB::raw('count(*) as total'))->get());

        $feedbacks = EventFeedback::where('event_id', $event->id)->with(['user'])->paginate(5);

        // get upcomming call vouchers
        $call_vouchers = VoucherBundle::whereIn('ticket_id', $ticket_ids)->whereNotNull('call_start_time')->where('call_start_time', '>=', Carbon::now())->orderBy('call_start_time')->get(['call_start_time as start_time', 'ticket_id'])->groupBy('ticket_id');
        $reservation_vouchers = VoucherBundle::whereIn('ticket_id', $ticket_ids)->whereNotNull('reservation_start_time')->where('reservation_start_time', '>=', Carbon::now())->orderBy('reservation_start_time')->get(['reservation_start_time as start_time', 'ticket_id'])->groupBy('ticket_id');
        // get upcomming live streams
        $live_streams = Ticket::whereIn('id', $ticket_ids)->where('is_live_stream', "1")->whereNotNull('stream_url')->get();

        return $this->sendResponse(
            [
                "organization" => $organization,
                "event" => $event,
                "tickets" => $tickets,
                "banner" => $banner,
                "feedbacks" => $feedbacks,
                "call_vouchers" => $call_vouchers,
                "reservation_vouchers" => $reservation_vouchers,
                "live_streams" => $live_streams,
                "cameraCrew" => isset($event_id) ? (int) $event_id === $event->id : false,
                "purchasers_count" => $purchasers_count
            ],
            'The event is successfully retrieved'
        );
    }


    function eventsByCategory()
    {
        $events = Event::upcoming()
            ->when(request('city'), function ($query, $state) {
                return $query->where('city', $state)
                    ->orWhere(function ($query) use ($state) {
                        return $query->whereHas('organization', function ($query) use ($state) {
                            return $query->where('city', $state);
                        });
                    });
            })
            ->when(request('category'), function ($query, $category) {
                return $query->whereHas('organization', function ($query) use ($category) {
                    return $query->where('category', $category);
                });
            })
            ->where('is_private', 0)
            ->paginate(15);
        $events->setPath('');

        $categories = Organization::groupBy('category')->limit(4)->get(['category'])->pluck('category');
        $states = Organization::groupBy('state')->limit(4)->get(['state'])->pluck('state');

        return $this->sendResponse($events, 'Events with respect to city or cateogry are successfully retrieved ');
    }

    function myEvents()
    {
        $user = auth()->user();
        $organization_ids = $user->organizations->pluck('id');
        $events = Event::whereIn('organization_id', $organization_ids)->orderBy('created_at', 'desc')->with('tickets.bundles')->with('attachments')->with('organization')->with('theatre')->get()->makeVisible(['investors', 'total_investment', 'expenses', 'sponsorship_sales']);
        $invited_events = EventMember::where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->whereHas('event')->with([
            'event.tickets.bundles',
            'event.attachments',
            'event.theatre',
            'event.organization' => function ($query) {
                $query->select('id', 'slug', 'category', 'name', 'slug', 'currency', 'app_fee_percentage');
            }
        ])->whereNotIn('event_id', $events->pluck('id')->toArray())->get();
        $invited_events->each(function ($member) {
            $member->event?->makeFinancialInfoVisible();
        });
        return $this->sendResponse(['events' => $events, 'invited_events' => $invited_events], 'success');
    }

    function createEvent(Request $request)
    {
        $this->validate($request, [
            'location' => 'required',
            'organization_id' => 'required'
        ]);
        $address = app('geocoder')->geocode($request->location)->get()->first();
        $request->merge([
            'slug' => str_slug($request->name),
            'zip' => isset($address) ? $address->getPostalCode() : null,
            'state' => isset($address) && count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
            'city' => isset($address) ? $address->getLocality() : null,
            'street' => isset($address) ? $address->getStreetName() : null,
            'street_number' => isset($address) ? $address->getStreetNumber() : null,
            'lat' => isset($address) ? $address->getCoordinates()->getLatitude() : null,
            'lng' => isset($address) ? $address->getCoordinates()->getLongitude() : null,
            'address' => $request->location,
        ]);

        $user = auth()->user();
        $organization = $user->organizations()->where('id', $request->organization_id)->firstOrFail();
        $fields = $this->validate($request, [
            'name' => 'required|min:3',
            'slug' => 'required|min:3',
            'description' => 'nullable',
            'image' => 'nullable',
            'is_timeless' => 'required',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date',
            'timezone' => 'required|string',
            'location_name' => '',
            'zip' => '',
            'state' => '',
            'city' => '',
            'street' => '',
            'street_number' => '',
            'lat' => '',
            'lng' => '',
            'address' => 'required|string',
            'is_private' => 'required',
            'video' => 'nullable',
            'fb_pixel_id' => 'nullable',
            'ga_measure_id' => 'nullable',
            'statement' => 'nullable',
            'prompt' => 'nullable',

            'total_investment' => 'nullable',
            'expenses' => 'nullable',
            'sponsorship_sales' => 'nullable',
            'investors' => 'nullable',
        ]);

        if (!isset($fields['image'])) {
            $fields['image'] = 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/09_Quality-128.png';
        }

        $fields['starts_at'] = new Carbon($fields['starts_at']);
        $fields['ends_at'] = new Carbon($fields['ends_at']);

        if (request('theatre')) {
            $theatre = Theatre::where('id', request('theatre'))->first();
            if ($theatre) {
                $fields['theatre_id'] = $theatre->id;
                $fields['theatre_json'] = $theatre->seat_json;
            }
        }

        if (isset($fields['investors'])) {
            $fields['investors'] = json_encode($fields['investors']);
        }

        $event = $organization->events()->create($fields);

        $request->user()->events()->attach([$event->id]);

        if ($request->pdf) {
            $eventAttachment = new EventAttachment();
            $eventAttachment->event_id = $event->id;
            $eventAttachment->name = $request->pdf['name'];
            $eventAttachment->file_name = $request->pdf['file_name'];
            $eventAttachment->save();
        }

        return $this->sendResponse($event, 'success');
    }

    function updateEvent(Request $request, Event $event)
    {
        if ($request->location) {
            $address = app('geocoder')->geocode($request->location)->get()->first();
            $request->merge([
                'zip' => isset($address) ? $address->getPostalCode() : null,
                'state' => isset($address) && count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
                'city' => isset($address) ? $address->getLocality() : null,
                'street' => isset($address) ? $address->getStreetName() : null,
                'street_number' => isset($address) ? $address->getStreetNumber() : null,
                'lat' => isset($address) ? $address->getCoordinates()->getLatitude() : null,
                'lng' => isset($address) ? $address->getCoordinates()->getLongitude() : null,
                'address' => $request->location,
            ]);
        }

        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("You have no permission to edit this event.", 401);
            }
        }
        $fields = $this->validate($request, [
            'name' => 'nullable',
            'description' => 'nullable',
            'image' => 'nullable',
            'is_timeless' => 'nullable',
            'starts_at' => 'nullable|date',
            'ends_at' => 'required|date',
            'timezone' => 'required|string',
            'location_name' => '',
            'zip' => '',
            'state' => '',
            'city' => '',
            'street' => '',
            'street_number' => '',
            'lat' => '',
            'lng' => '',
            'address' => 'nullable|string',
            'is_private' => 'nullable',
            'video' => 'nullable',
            'fb_pixel_id' => 'nullable',
            'ga_measure_id' => 'nullable',
            'statement' => 'nullable',
            'prompt' => 'nullable',
            'organization_id' => 'nullable',

            'total_investment' => 'nullable',
            'expenses' => 'nullable',
            'sponsorship_sales' => 'nullable',
            'investors' => 'nullable',
        ]);
        if (isset($fields['starts_at']) && $fields['starts_at']) {
            $fields['starts_at'] = new Carbon($fields['starts_at']);
        }
        if (isset($fields['ends_at']) && $fields['ends_at']) {
            $fields['ends_at'] = new Carbon($fields['ends_at']);
        }
        if (isset($request->name) && $request->name) {
            $fields['slug'] = str_slug($fields['name']);
        }

        // if (request('theatre')) {
        //     $theatre = Theatre::where('id', request('theatre'))->first();
        //     if ($theatre && $theatre->id !== $event->theatre_id) {
        //         $fields['theatre_id'] = $theatre->id;
        //         $fields['theatre_json'] = $theatre->seat_json;
        //     }
        // } else {
        //     $fields['theatre_id'] = null;
        //     $fields['theatre_json'] = null;
        // }

        if ($request->pdf) {
            EventAttachment::where('event_id', $event->id)->delete();
            $eventAttachment = new EventAttachment();
            $eventAttachment->event_id = $event->id;
            $eventAttachment->name = $request->pdf['name'];
            $eventAttachment->file_name = $request->pdf['file_name'];
            $eventAttachment->save();
        } else {
            EventAttachment::where('event_id', $event->id)->delete();
        }

        if (isset($fields['investors'])) {
            $fields['investors'] = json_encode($fields['investors']);
        }

        $event->update($fields);

        return $this->sendResponse($event, 'success');
    }

    function deleteEvent(Event $event)
    {
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            return $this->sendError("You have no permission to edit this event.", 401);
        }
        $event->delete();
        return $this->sendResponse(null, 'success');
    }

    function createTicket(Request $request, Event $event)
    {
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("You have no permission to create a ticket in this event!", 401);
            }
        }
        $fields = $this->validate($request, [
            'name' => 'required|min:3',
            'description' => 'nullable',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',

            'is_live_stream' => 'required',
            'is_call' => 'required',
            'is_reservation' => 'required',

            'is_seat' => 'nullable',
            'is_donation' => 'nullable',
            'is_subscribe' => 'required',
            'discount_percent' => 'nullable|numeric',

            'is_media' => 'required',
            'media_url' => 'nullable',
            'media_type' => 'nullable',
            'is_media_downloadable' => 'nullable'
        ], []);

        $fields['slug'] = str_slug($fields['name']);

        if ($request['is_donation'])
            $fields['price'] = 0;
        $fields['price'] = $fields['price'] * 100;

        if ($request['is_live_stream']) {
            $this->validate($request, [
                'stream_type' => 'required',
                'stream_url' => 'nullable',
                'is_private' => 'required',
            ]);
            $fields['is_private'] = $request['is_private'];
            $fields['stream_type'] = $request['stream_type'];
            if ($request['stream_type'] === 'eventssmarter') {
                $stream = Helpers::createLiveStream($fields['name']);
                $fields['stream_url'] = $stream['host'];
                $fields['attendee_url'] = $stream['view'];
            } else if ($request['stream_type'] === 'rtmp') {
                $stream = Helpers::createCloudflareLiveInput($event->name . ' - ' . $fields['name']);
                $fields['rtmp_uid'] = $stream['uid'];
                $fields['rtmp_url'] = $stream['url'];
                $fields['rtmp_key'] = $stream['key'];
            } else if ($request['stream_type'] === 'same') {
                $this->validate($request, [
                    'stream_same_ticket_id' => 'required',
                ]);
                $same_ticket = Ticket::where('id', $request['stream_same_ticket_id'])->first();
                if (!$same_ticket) {
                    return $this->sendError('same ticket not exist');
                }
                $fields['stream_type'] = $same_ticket->stream_type;
                $fields['stream_url'] = $same_ticket->stream_url;
                $fields['attendee_url'] = $same_ticket->attendee_url;
                $fields['rtmp_uid'] = $same_ticket->rtmp_uid;
                $fields['rtmp_url'] = $same_ticket->rtmp_url;
                $fields['rtmp_key'] = $same_ticket->rtmp_key;
            } else {
                $this->validate($request, [
                    'stream_url' => 'required',
                ]);
                $fields['stream_url'] = $request['stream_url'];
            }
        }

        if ($request['is_call']) {
            $this->validate($request, [
                'call_available_start_time' => 'required',
                'call_available_end_time' => 'required',
                'call_available_weekends' => 'required',
                'call_time' => 'required',
            ]);
            $fields['call_available_start_time'] = $request->call_available_start_time;
            $fields['call_available_end_time'] = $request->call_available_end_time;
            $fields['call_available_weekends'] = $request->call_available_weekends;
            $fields['call_time'] = $request->call_time['value'];
            $meeting = Helpers::createMeeting($fields['name']);
            $fields['stream_url'] = $meeting['host'];
            $fields['attendee_url'] = $meeting['view'];
        }

        if ($request['is_reservation']) {
            $this->validate($request, [
                'reservation_available_start_time' => 'required',
                'reservation_available_end_time' => 'required',
                'reservation_available_weekends' => 'required',
                'reservation_time' => 'required'
            ]);
            $fields['reservation_available_start_time'] = $request->reservation_available_start_time;
            $fields['reservation_available_end_time'] = $request->reservation_available_end_time;
            $fields['reservation_available_weekends'] = $request->reservation_available_weekends;
            $fields['reservation_time'] = $request->reservation_time;
        }

        $ticket = $event->tickets()->create($fields);

        if (isset($request->ticket_prompts) && count($request->ticket_prompts) > 0) {
            TicketPrompt::create([
                'ticket_id' => $ticket->id,
                'prompt_json' => json_encode($request->ticket_prompts),
            ]);
        }

        if (isset($request->ticket_bundles) && count($request->ticket_bundles) > 0) {
            foreach ($request->ticket_bundles as $bundle) {
                TicketBundle::create([
                    'ticket_id' => $ticket->id,
                    'count' => $bundle['count'],
                    'price' => $bundle['origin_price'] * 100,
                    'bundle_name' => $bundle['bundle_name'],
                ]);
            }
        }

        return $this->sendResponse($ticket, 'success');
    }

    function updateTicket(Request $request, Event $event, Ticket $ticket)
    {
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("You have no permission to update this ticket!", 401);
            }
        }
        $fields = $this->validate($request, [
            'name' => 'nullable',
            'description' => 'nullable',
            'quantity' => 'nullable',
            'price' => 'nullable',

            'is_live_stream' => 'required',
            'is_call' => 'required',
            'is_reservation' => 'required',

            'is_seat' => 'nullable',
            'is_donation' => 'nullable',
            'is_subscribe' => 'required',
            'discount_percent' => 'nullable|numeric',

            'is_media' => 'required',
            'media_url' => 'nullable',
            'media_type' => 'nullable',
            'is_media_downloadable' => 'nullable'
        ], []);

        if (isset($request['is_donation']) && $request['is_donation']) {
            $fields['price'] = 0;
        }
        if (isset($request['price']) && $request['price']) {
            $fields['price'] = $fields['price'] * 100;
        }
        if (isset($request['name']) && $request['name']) {
            $fields['slug'] = str_slug($fields['name']);
        }
        if ($request['is_live_stream']) {
            $this->validate($request, [
                'stream_type' => 'required',
                'stream_url' => 'nullable',
                'is_private' => 'required',
            ]);
            $fields['is_private'] = $request['is_private'];
            $fields['stream_type'] = $request['stream_type'];
            $fields['stream_url'] = null;
            $fields['attendee_url'] = null;
            $fields['rtmp_uid'] = null;
            $fields['rtmp_url'] = null;
            $fields['rtmp_key'] = null;
            if ($request['stream_type'] === 'eventssmarter' && $ticket->stream_type !== 'eventssmarter') {
                $stream = Helpers::createLiveStream($fields['name']);
                $fields['stream_url'] = $stream['host'];
                $fields['attendee_url'] = $stream['view'];
            } else if ($request['stream_type'] === 'rtmp' && $ticket->stream_type !== 'rtmp') {
                $stream = Helpers::createCloudflareLiveInput($event->name . ' - ' . $fields['name']);
                $fields['rtmp_uid'] = $stream['uid'];
                $fields['rtmp_url'] = $stream['url'];
                $fields['rtmp_key'] = $stream['key'];
            } else if ($request['stream_type'] === 'same') {
                $this->validate($request, [
                    'stream_same_ticket_id' => 'required',
                ]);
                $same_ticket = Ticket::where('id', $request['stream_same_ticket_id'])->first();
                if (!$same_ticket) {
                    return $this->sendError('same ticket not exist');
                }
                $fields['stream_type'] = $same_ticket->stream_type;
                $fields['stream_url'] = $same_ticket->stream_url;
                $fields['attendee_url'] = $same_ticket->attendee_url;
                $fields['rtmp_uid'] = $same_ticket->rtmp_uid;
                $fields['rtmp_url'] = $same_ticket->rtmp_url;
                $fields['rtmp_key'] = $same_ticket->rtmp_key;
            } else {
                $this->validate($request, [
                    'stream_url' => 'required',
                ]);
                $fields['stream_url'] = $request['stream_url'];
            }
        }

        if (isset($request['is_call']) && $request['is_call']) {
            $this->validate($request, [
                'call_available_start_time' => 'required',
                'call_available_end_time' => 'required',
                'call_available_weekends' => 'required',
                'call_time' => 'required',
            ]);
            $fields['call_available_start_time'] = $request->call_available_start_time;
            $fields['call_available_end_time'] = $request->call_available_end_time;
            $fields['call_available_weekends'] = $request->call_available_weekends;
            $fields['call_time'] = $request->call_time;
            $meeting = Helpers::createMeeting($fields['name']);
            $fields['stream_url'] = $meeting['host'];
            $fields['attendee_url'] = $meeting['view'];
        }

        if (isset($request['is_reservation']) && $request['is_reservation']) {
            $this->validate($request, [
                'reservation_available_start_time' => 'required',
                'reservation_available_end_time' => 'required',
                'reservation_available_weekends' => 'required',
                'reservation_time' => 'required'
            ]);
            $fields['reservation_available_start_time'] = $request->reservation_available_start_time;
            $fields['reservation_available_end_time'] = $request->reservation_available_end_time;
            $fields['reservation_available_weekends'] = $request->reservation_available_weekends;
            $fields['reservation_time'] = $request->reservation_time;
        }

        $ticket->update($fields);

        if (isset($request->ticket_prompts) && count($request->ticket_prompts) > 0) {
            $ticket_prompt = TicketPrompt::where('ticket_id', $ticket->id)->first();
            if ($ticket_prompt) {
                $ticket_prompt->update(['prompt_json' => json_encode($request->ticket_prompts)]);
            } else {
                TicketPrompt::create([
                    'ticket_id' => $ticket->id,
                    'prompt_json' => json_encode($request->ticket_prompts),
                ]);
            }
        } else {
            $ticket_prompt = TicketPrompt::where('ticket_id', $ticket->id)->first();
            if ($ticket_prompt) {
                $ticket_prompt->delete();
            }
        }

        if (isset($request->ticket_bundles) && count($request->ticket_bundles) > 0) {
            $bundle_ids = TicketBundle::where('ticket_id', $ticket->id)->pluck('id')->toArray();
            foreach ($request->ticket_bundles as $bundle) {
                if (isset($bundle['id'])) {
                    $ticket_bundle = TicketBundle::where('id', $bundle['id'])->first();
                    if ($ticket_bundle) {
                        $bundle_ids = array_filter($bundle_ids, function ($value) use ($bundle) {
                            return $value !== $bundle['id'];
                        });
                        $bundle_ids = array_values($bundle_ids);
                        $ticket_bundle->update(['count' => $bundle['count'], 'price' => $bundle['origin_price'] * 100, 'bundle_name' => $bundle['bundle_name']]);
                    }
                } else {
                    TicketBundle::create([
                        'ticket_id' => $ticket->id,
                        'count' => $bundle['count'],
                        'price' => $bundle['origin_price'] * 100,
                        'bundle_name' => $bundle['bundle_name'],
                    ]);
                }
            }
            if (count($bundle_ids) > 0) {
                TicketBundle::whereIn('id', $bundle_ids)->delete();
            }
        } else {
            TicketBundle::where('ticket_id', $ticket->id)->delete();
        }

        return $this->sendResponse($ticket, 'success');
    }

    function deleteTicket(Event $event, Ticket $ticket)
    {
        $user = auth()->user();
        if (!$user->can('update', $event) || $ticket->event->id !== $event->id) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("You have no permission to delete this ticket!", 401);
            }
        }
        $ticket->delete();
        return $this->sendResponse(null, 'success');
    }

    function seatVoucherPreview($event_id, VoucherBundle $voucher)
    {
        $event = Event::with('theatre')->findOrFail($event_id);
        return $this->sendResponse(['event' => $event, 'voucher' => $voucher], 'success');
    }

    function sortTicket(Event $event, Request $request)
    {
        $this->validate($request, [
            'sort_ticket_ids' => 'required|array'
        ]);
        try {
            $tickets = $event->tickets;
            foreach ($tickets as $ticket) {
                $ticket->sort_priority = array_search($ticket->id, $request->sort_ticket_ids);
                $ticket->save();
            }
            return $this->sendResponse($tickets, 'success');
        } catch (\Throwable $th) {
            return $this->sendError(null, 422);
        }
    }

    function sendNotification(Event $event, Request $request)
    {
        $fields = $this->validate($request, [
            'subject' => 'required',
            'content' => 'required',
            'image_url' => 'nullable'
        ]);
        try {
            $user = auth()->user();
            if (!$user->can('update', $event)) {
                $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
                if (!$member) {
                    return $this->sendError("No Permission", 401);
                }
            }
            $client = new \GuzzleHttp\Client();
            $response = $client->post(env('APIV2_URL') . '/api/notification/send/event/' . $event->id, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'user_id' => $user->id,
                    'subject' => $fields['subject'],
                    'content' => $fields['content'],
                    'image_url' => isset($fields['image_url']) ? $fields['image_url'] : '',
                    'shop_image_url' => isset($event->ad_image) ? $event->ad_image : $event->organization->ad_image,
                    'shop_url' => isset($event->ad_link) ? $event->ad_link : $event->organization->ad_link,
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return $this->sendResponse($response, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function sendNotificationToAll(Request $request)
    {
        $fields = $this->validate($request, [
            'subject' => 'required',
            'content' => 'required',
            'image_url' => 'nullable'
        ]);
        try {
            $user = auth()->user();
            $client = new \GuzzleHttp\Client();
            $response = $client->post(env('APIV2_URL') . '/api/notification/send/organizer/' . $user->id, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'subject' => $fields['subject'],
                    'content' => $fields['content'],
                    'image_url' => isset($fields['image_url']) ? $fields['image_url'] : '',
                    'shop_image_url' => 'https://www.eventssmarter.com/images/branded.png',
                    'shop_url' => 'https://www.athleticjunction.com/store/',
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return $this->sendResponse($response, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function sendInviteCameraTeamEmail(Ticket $ticket, Request $request)
    {
        $fields = $this->validate($request, [
            'email' => 'required',
        ]);
        try {
            $user = auth()->user();
            if (!$user->can('update', $ticket->event)) {
                $member = EventMember::where('event_id', $ticket->event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
                if (!$member) {
                    return $this->sendError("No Permission", 401);
                }
            }
            $subject = "Invitation as Camera Production Team at " . $ticket->event->name;
            if ($ticket->stream_type === 'eventssmarter') {
                $content = 'Live Stream Information <br/><br/><br/> Live Stream URL: https://live.eventssmarter.com/' . $ticket->stream_url . '<br/><br/> Attendee URL: https://live.eventssmarter.com/' . $ticket->attendee_url;
            } else if ($ticket->stream_type === 'rtmp') {
                $content = 'Live Stream RTMP configuration. <br/><br/><br/> RTMP URL: ' . $ticket->rtmp_url . '<br/><br/> Stream Key: ' . $ticket->rtmp_key;
            } else {
                return $this->sendError('ticket stream type should be EventsSmarter stream or RTMP with OBS', 422);
            }
            $client = new \GuzzleHttp\Client();
            $response = $client->post(env('APIV2_URL') . '/api/notification/stream/invite_camera_team', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'subject' => $subject,
                    'content' => $content,
                    'image_url' => $ticket->event->image,
                    'event_url' => env('FRONTEND_URL') . '/' . $ticket->event->organization->slug . '/' . $ticket->event->slug . '_' . $ticket->event->id,
                    'email' => $fields['email'],
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return $this->sendResponse($response, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function getMembers(Event $event)
    {
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            return $this->sendError("No Permission", 401);
        }
        $members = EventMember::where('event_id', $event->id)->with('user')->get();
        return $this->sendResponse($members, 'success');
    }

    function addMember(Request $request, Event $event)
    {
        $fields = $this->validate($request, [
            'email' => 'required|email',
            'role' => 'required', // admin, member
        ]);
        try {
            $user = auth()->user();
            if (!$user->can('update', $event)) {
                return $this->sendError("No Permission", 401);
            }
            //check if member email already exist
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $fields['email'])->first();
            if ($member) {
                return $this->sendError('Already sent invitation', 422);
            }
            $invited_user = User::where('email', $fields['email'])->first();
            EventMember::create([
                'event_id' => $event->id,
                'invited_user_email' => $fields['email'],
                'invited_user_id' => $invited_user ? $invited_user->id : null,
                'user_role' => $fields['role'],
                'invitation_accept_status' => 'pending'
            ]);

            $subject = "Invitation at " . $event->name;
            $user_role = $fields['role'] === "admin" ? 'Administrator' : 'Member';
            $client = new \GuzzleHttp\Client();
            $response = $client->post(env('APIV2_URL') . '/api/notification/event/invite_member', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'subject' => $subject,
                    'user_role' => $user_role,
                    'event_name' => $event->name,
                    'event_url' => env('FRONTEND_URL') . '/' . $event->organization->slug . '/' . $event->slug . '_' . $event->id,
                    'invite_url' => env('FRONTEND_URL') . '/events/' . $event->id . '/invitations?email=' . $fields['email'],
                    'email' => $fields['email'],
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            return $this->sendResponse($response, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function deleteMember(Request $request, Event $event, $member_id)
    {
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            return $this->sendError("No Permission", 401);
        }
        $member = EventMember::where('id', $member_id)->where('event_id', $event->id)->first();
        if ($member) {
            $member->delete();
            return $this->sendResponse(null, 'success');
        }
        return $this->sendError('Member not exist');
    }

    function updateMemberRole(Request $request, Event $event)
    {
        $fields = $this->validate($request, [
            'member_id' => 'required',
            'role' => 'required', // admin, member
        ]);
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            return $this->sendError("No Permission", 401);
        }
        $member = EventMember::where('id', $fields['member_id'])->where('event_id', $event->id)->first();
        if ($member) {
            $member->update(['user_role' => $fields['role']]);
            return $this->sendResponse(null, 'success');
        }
        return $this->sendError('Member not exist');
    }

    function MemberAccept(Request $request, Event $event)
    {
        $fields = $this->validate($request, [
            'member_email' => 'required',
        ]);
        $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $fields['member_email'])->where('invitation_accept_status', 'pending')->first();
        if ($member) {
            $member->update(['invitation_accept_status' => 'accepted']);
            return $this->sendResponse(null, 'success');
        }
        return $this->sendError('The invitation has expired!', 422);
    }

    function MemberDeny(Request $request, Event $event)
    {
        $fields = $this->validate($request, [
            'member_email' => 'required',
        ]);
        $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $fields['member_email'])->where('invitation_accept_status', 'pending')->first();
        if ($member) {
            $member->update(['invitation_accept_status' => 'denied']);
            $member->delete();
            return $this->sendResponse(null, 'success');
        }
        return $this->sendError('The invitation has expired!', 422);
    }

    function MemberLeave(Request $request, Event $event)
    {
        $user = auth()->user();
        $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->first();
        if ($member) {
            $member->delete();
            return $this->sendResponse(null, 'success');
        }
        return $this->sendError('Member not exist');
    }

    function duplicateEvent(Event $event)
    {
        try {
            $user = auth()->user();
            if (!$user->can('update', $event)) {
                return $this->sendError("No Permission", 401);
            }
            //event
            $new_event = $event->replicate();
            $new_event->save();

            auth()->user()->events()->attach([$new_event->id]);

            // attachment
            $files = EventAttachment::where('event_id', $event->id)->latest()->get();
            $files->each(function ($item, $key) use ($new_event) {
                $copy = $item->replicate();
                $copy->event_id = $new_event->id;
                $copy->save();
            });

            // tickets
            $tickets = $event->tickets()->get();
            $tickets->each(function ($item, $key) use ($new_event) {
                $copy = $item->replicate();
                $copy->event_id = $new_event->id;
                $copy->save();
            });

            // banners
            $banners = Banner::where('link_id', $event->id)->where('link_type', '0')->get();
            $banners->each(function ($item, $key) use ($new_event) {
                $copy = $item->replicate();
                $copy->link_id = $new_event->id;
                $copy->save();
            });
            return $this->sendResponse($new_event, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage());
        }
    }

    public function generateEventDescription(Request $request)
    {
        $fields = $this->validate($request, [
            'name' => 'required|string|max:255',
            'date' => 'nullable|string|max:100',
            'location' => 'required|string|max:255',
        ]);

        // Create the prompt for the AI API
        $prompt = "Generate an engaging event description for an event with the following details:\n"
            . "Title: {$fields['name']}\n";
        if ($fields['date']) {
            $prompt .= "Date: {$fields['date']}\n";
        }
        if ($fields['location']) {
            $prompt .= "Location: {$fields['location']}\n\n";
        }

        $prompt .= "Please include details about what attendees can expect, the atmosphere, and any unique features of the event.";

        $apiKey = env('MISTRAL_API_KEY');

        $client = new \GuzzleHttp\Client();

        try {
            // Send the POST request to the OpenAI API endpoint
            $response = $client->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'open-mixtral-8x22b',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ],
            ]);

            // Decode the response
            $body = json_decode($response->getBody(), true);
            $description = trim($body['choices'][0]['message']['content'] ?? '');

            return $this->sendResponse($description, 'success');
        } catch (\Exception $th) {

            return $this->sendError($th->getMessage(), 500);
        }
    }
}
