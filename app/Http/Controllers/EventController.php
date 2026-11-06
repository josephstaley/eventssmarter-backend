<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MyEvents;
use App\Models\Ticket;
use App\Models\Voucher;
use App\Models\VoucherBundle;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Banner;
use App\Models\Organization;
use App\Models\EventFeedback;
use App\Models\EventAttachment;
use App\Models\Theatre;
use App\Models\PromoCode;
use App\Models\PendingSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mail;
use DataTables;
use App\Jobs\SendNotification;
use RolfHaug\TokenAuth\LoginToken;
use Illuminate\Support\Facades\Crypt;
use App\Classes\AgoraDynamicKey\RtcTokenBuilder;
use Illuminate\Support\Facades\Cookie;
use DB;
use App\Notifications\EventAdvertise;
use Notification;
use Pusher\Pusher;

class EventController extends Controller
{
    public function index(MyEvents $myEvents, $choice = null)
    {
        $events = Event::upcoming()
            ->with('organization')
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

        return view('events.index', compact('events', 'categories', 'states', 'choice'))->with('noShop', true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Organization $organization)
    {
        $theatres = Theatre::get();
        return view('events.create', compact('organization', 'theatres'));
    }

    private function parseDateFrom(array $array)
    {
        $date = collect($array)->filter();

        if ($date->count() != 3) {
            return null;
        }

        return new Carbon($date->implode(' '));
    }

    public function mergeGeocodedAddress($request)
    {
        $address = app('geocoder')->geocode($request->autocomplete)->get()->first();
        $request->merge([
            'slug' => str_slug($request->name),
            'zip' => isset($address) ? $address->getPostalCode() : null,
            'state' => isset($address) && count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
            'city' => isset($address) ? $address->getLocality() : null,
            'street' => isset($address) ? $address->getStreetName() : null,
            'street_number' => isset($address) ? $address->getStreetNumber() : null,
            'lat' => isset($address) ? $address->getCoordinates()->getLatitude() : null,
            'lng' => isset($address) ? $address->getCoordinates()->getLongitude() : null,
            'address' => $request->autocomplete,
        ]);
    }

    public function store(Request $request, Organization $organization)
    {
        // validation for get geocode
        $fields = $this->validate($request, [
            'autocomplete' => 'required',
        ]);
        $this->mergeGeocodedAddress($request);

        $organization = auth()->user()->organizations()->where('slug', $organization->slug)->firstOrFail();
        $fields = (object)$this->validate($request, [
            'name' => 'required|min:3',
            'description' => 'nullable',
            'image' => 'nullable|image',
            'starts_at.date' => 'required|date',
            'starts_at.time' => 'required|string',
            'starts_at.am' => 'required|string',
            'ends_at.date' => 'required|date',
            'ends_at.time' => 'required|string',
            'ends_at.am' => 'required|string',
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
            'video' => 'nullable|mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi',
        ]);

        if ($fields->is_private === 'on') {
            $fields->is_private = 1;
        }

        $fields = $this->validateDates($fields);

        if (!isset($fields->image)) {
            $fields->image = 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/09_Quality-128.png';
        } else {
            $file = $request->file('image')->storePublicly('events');
            $fields->image = Storage::url($file);
        }

        if (isset($fields->video)) {
            $video_file = $request->file('video')->storePublicly('events');
            $fields->video = Storage::url($video_file);
        }

        if (request('fb_pixel_id')) {
            $fields->fb_pixel_id = request('fb_pixel_id');
        }
        if (request('ga_measure_id')) {
            $fields->ga_measure_id = request('ga_measure_id');
        }

        if ($request['statement']) {
            $fields->statement = $request['statement'];
        }
        if ($request['prompt']) {
            $fields->prompt = $request['prompt'];
        }

        if (request('theatre')) {
            $theatre = Theatre::where('id', request('theatre'))->first();
            if ($theatre) {
                $fields->theatre_id = $theatre->id;
                $fields->theatre_json = $theatre->seat_json;
            }
        }

        $fields->slug = str_slug($fields->name);

        $event = $organization->events()->create((array)$fields);

        $request->user()->events()->attach([$event->id]);

        if ($request->file('pdf')) {
            $this->insertPdf($request, $event->id);
        }

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            array(
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'encrypted' => true
            )
        );
        $pushDatas = array(
            "lat" => $event->lat,
            "lng" => $event->lng,
            "eventName" => $event->name,
            "orgName" => $organization->name
        );
        // $pushDatas->lat = $event->lat;
        // $pushDatas->lng = $event->lng;
        // $pushDatas->eventName = $event->name;
        // $pushDatas->orgName = $organization->name;
        $pusher->trigger('event-stored', 'event-stored-event', $pushDatas);

        return redirect($event->link('events.show'))
            ->with('message', 'Event was created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event $event
     * @return \Illuminate\Http\Response
     */
    public function show($organization, $title, Event $event, Request $request)
    {
        $event = Event::with(['organization'])->find($event->id);
        if (isset($request['token'])) {
            try {
                $event_id = Crypt::decryptString($request['token']);
                Cookie::queue('event_token', $request['token'], 60 * 72);
            } catch (\Throwable $th) {
            }
        }
        if ($request->cookie('event_token')) {
            $event_id = Crypt::decryptString($request->cookie('event_token'));
        }
        $organization = Organization::with('users')->where('slug', $organization)->firstOrFail();

        if (request('admin_edit') && auth()->user() && auth()->user()->admin) {
            auth()->login($organization->users()->first());
        }

        if ($event->tickets()->count() == 1 && request('checkout')) {
            return redirect()->route('checkout.decide', ['id' => $event->tickets->first()->id, 'quantity' => 1]);
        }
        $id = $event->id;
        $orgId = $event->organization_id;
        $orgBanner = Banner::where('link_id', $orgId)->where('link_type', '1')->whereNull('is_main_banner')->get();
        $model = Banner::where('link_id', $id)->where('link_type', '0')->whereNull('is_main_banner')->get();
        $banner = array();
        foreach ($orgBanner as $orgItem) {
            array_push($banner, $orgItem);
        }
        foreach ($model as $eventItem) {
            array_push($banner, $eventItem);
        }
        $modelMainBanner = Banner::where('link_id', $id)->where('link_type', '0')->whereNotNull('is_main_banner')->get()->first();
        // get tickets of the event
        $tickets = Ticket::where('event_id', $event->id)->get();
        $ticket_ids = [];
        $video_call_ticket_available = 0;
        foreach ($tickets as $ticket) {
            $ticket_ids[] = $ticket->id;
            if ($ticket->is_call) {
                $video_call_ticket_available = 1;
            }
        }

        // get vouchers ( purchased ones ) of the event
        $purchasers_count = VoucherBundle::whereIn('ticket_id', $ticket_ids)->groupBy('user_id')->where('voucher_bundles.created_at', '<', Carbon::now()->subHours($organization->sales_time_delay)->toDateTimeString())->select('user_id', DB::raw('count(*) as total'));

        if (auth()->user() && !auth()->user()->admin) {
            $purchasers_count = $purchasers_count->where(function ($q) {
                $q->where('voucher_bundles.is_hide_on_sales', '!=', 1)->orWhereNull('voucher_bundles.is_hide_on_sales');
            });
        }
        $purchasers_count = count($purchasers_count->get());

        $feedbacks = EventFeedback::where('event_id', $id)->with(['user'])->paginate(5);

        // get upcomming call vouchers
        $call_vouchers = VoucherBundle::whereIn('ticket_id', $ticket_ids)->whereNotNull('call_start_time')->where('call_start_time', '>=', Carbon::now())->orderBy('call_start_time')->get();
        // get upcomming live streams
        $live_streams = Ticket::whereIn('id', $ticket_ids)->where('is_live_stream', "1")->whereNotNull('stream_url')->get();

        $login_token = '';
        $encrypted_login_token = '';
        if (auth()->user()) {
            $login_token = LoginToken::generate(auth()->user(), true);
            $encrypted_login_token = Crypt::encryptString($login_token);
        }
        if (isset($event_id) && (int)$event_id === $event->id) {
            $login_token = LoginToken::generate($event->organization->users()->first(), true);
            $encrypted_login_token = Crypt::encryptString($login_token);
        }
        $showPendingSubscription = false;
        if ($request['subscription_id']) {
            $pendingSubscription = PendingSubscription::where('subscription_id', $request['subscription_id'])->withTrashed()->first();
            if ($pendingSubscription && !$pendingSubscription->notified) {
                $pendingSubscription->notified = 1;
                $pendingSubscription->save();
                $showPendingSubscription = true;
            }
        }
        return view('events.show')
            ->with('organization', $organization)
            ->with('event', $event)
            ->with('banner', $banner)
            ->with('mainBanner', $modelMainBanner)
            ->with('feedbacks', $feedbacks)
            ->with('noShop', true)
            ->with('video_call_ticket_available', $video_call_ticket_available)
            ->with('call_vouchers', $call_vouchers)
            ->with('login_token', $login_token)
            ->with('encrypted_login_token', $encrypted_login_token)
            ->with('live_streams', $live_streams)
            ->with('cameraCrew', isset($event_id) ? (int)$event_id === $event->id : false)
            ->with('purchasers_count', $purchasers_count)
            ->with('promo_code', $request->get('promo_code'))
            ->with('showPendingSubscription', $showPendingSubscription);
    }

    public function advertise(Request $request, Organization $organization, $event_id)
    {
        $event = Event::where('organization_id', $organization->id)->where('id', $event_id)->get()->first();
        $promo_codes = PromoCode::where('organization_id', $organization->id)->get();

        foreach ($promo_codes as $code) {
            Notification::route('mail', $code->affiliate_email)->notify(new EventAdvertise($organization, $event, $code->promo_code));
        }

        return redirect($event->link('events.show'))->with('message', 'Advertise link was sent to promoters!');
    }

    public function getEventPurchasers(Request $request)
    {
        $event_id = $request->get('event_id');
        $event = Event::where('id', $event_id)->first();

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = count(VoucherBundle::where('event_id', $event_id)->groupBy('voucher_bundles.user_id')->select('voucher_bundles.user_id', DB::raw('count(*) as total'))->get());
        $totalRecordswithFilter = count(VoucherBundle::where('event_id', $event_id)
            ->join('users', 'users.id', 'voucher_bundles.user_id')
            ->groupBy('voucher_bundles.user_id')
            ->select('voucher_bundles.user_id', DB::raw('count(*) as total'))
            ->where('voucher_bundles.updated_at', '<', Carbon::now()->subHours($event->organization->sales_time_delay)->toDateTimeString())
            ->where(function ($query) use ($searchValue) {
                $query->where('users.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.email', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.phone_number', 'like', '%' . $searchValue . '%');
            })->get());

        // Fetch records
        $records = VoucherBundle::where('event_id', $event_id)
            ->join('users', 'users.id', 'voucher_bundles.user_id')
            ->groupBy('voucher_bundles.user_id')
            ->orderBy($columnName, $columnSortOrder)
            ->where('voucher_bundles.updated_at', '<', Carbon::now()->subHours($event->organization->sales_time_delay)->toDateTimeString())
            ->where(function ($query) use ($searchValue) {
                $query->where('users.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.email', 'like', '%' . $searchValue . '%')
                    ->orWhere('users.phone_number', 'like', '%' . $searchValue . '%');
            })
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            if (!$name) {
                $name = $record->fname . ' ' . $record->lname;
            }
            $email = $record->email;
            $phone_number = $record->phone_number;

            $data_arr[] = array(
                "id" => $id,
                "avatar" => $record->avatar ? $record->avatar : 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png',
                "name" => $name,
                "email" => $email,
                "phone_number" => $phone_number,
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        echo json_encode(
            $response
        );

        exit;
    }

    /**
     * @param Organization $organization
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Organization $organization, $id)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization->slug)->firstOrFail();

        $event = Event::find($id);

        $files = EventAttachment::where('event_id', $id)->latest()->get();

        $theatres = Theatre::get();

        return view('events.edit', compact('organization', 'event', 'files', 'theatres'));
    }


    /**
     * @param Organization $organization
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function notification(Request $request, Organization $organization, $id)
    {
        if (auth()->user()->admin) {
            $organization = Organization::where('slug', $organization->slug)->firstOrFail();
        } else $organization = auth()->user()->organizations()->where('slug', $organization->slug)->firstOrFail();

        $fields = $this->validate($request, [
            'subject' => 'required',
            'message' => 'required|min:3',
            'attach_img' => 'nullable|image',
            'attach_file' => 'nullable|file',
        ]);
        $img_url = '';
        $file_url = '';
        if (isset($fields['attach_img'])) {
            $image = $request->file('attach_img')->storePublicly('events');
            $img_url = Storage::url($image);
        }
        if (isset($fields['attach_file'])) {
            $file = $request->file('attach_file')->storePublicly('events');
            $file_url = Storage::url($file);
        }

        $event = Event::find($id);

        // get tickets of the event
        $tickets = Ticket::where('event_id', $event->id)->get();
        $ticket_ids = [];
        foreach ($tickets as $ticket) {
            $ticket_ids[] = $ticket->id;
        }

        dispatch(new SendNotification($ticket_ids, $fields['subject'], $fields['message'], $img_url, $file_url, auth()->user()->admin));

        // $voucher_ids = [];
        // $now = Carbon::now()->addSeconds(20);

        // foreach ($vouchers as $voucher) {
        //     // $voucher_ids[] = $voucher->id;

        //     // if($voucher->user->device_token && $voucher->user->device_token !== ''){
        //     //     $client = new \GuzzleHttp\Client();
        //     //     $client->post('https://fcm.googleapis.com/fcm/send', [
        //     //         'headers' => [
        //     //             'Content-Type' => 'application/json',
        //     //             'Authorization' => 'key=AAAAD_S31Os:APA91bEhgz_fxDoNWF9T14HG9p_gOBrmj181hJwFxa1CFgkTCwtBZt3Qfaf29xph3k3Xsv8RqsIKLvI3_dF4p8SBuU0iF_lysF4SPxuvk5h4m84slY1g4oNpQqZbwSXmdJAfgV2QBMjD'
        //     //         ],
        //     //         'body' => '{
        //     //             "to":"'.$voucher->user->device_token.'",
        //     //             "notification":{
        //     //                 "body":"'.$fields['message'].'"
        //     //             }
        //     //         }'
        //     //     ]);
        //     // }
        // }

        return redirect($event->link('events.show'));
    }

    /**
     * @param Request $request
     * @param Organization $organization
     * @param $slug
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, Organization $organization, $id)
    {
        // validation for get geocode
        $fields = $this->validate($request, [
            'autocomplete' => 'required',
        ]);
        $this->mergeGeocodedAddress($request);

        $organization = auth()->user()->organizations()->where('slug', $organization->slug)->firstOrFail();

        $fields = $this->validate($request, [
            'name' => 'required|min:3',
            'description' => 'nullable',
            'image' => 'nullable|image',
            'starts_at.date' => 'required|date',
            'starts_at.time' => 'required|string',
            'starts_at.am' => 'required|string',
            'ends_at.date' => 'required|date',
            'ends_at.time' => 'required|string',
            'ends_at.am' => 'required|string',
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
            'video' => 'nullable|mimetypes:video/x-ms-asf,video/x-flv,video/mp4,application/x-mpegURL,video/MP2T,video/3gpp,video/quicktime,video/x-msvideo,video/x-ms-wmv,video/avi',
        ]);

        if ($fields['is_private'] === 'on') {
            $fields['is_private'] = 1;
        }

        $fields['starts_at'] = $this->parseDateFrom($fields['starts_at']);

        $fields['ends_at'] = $this->parseDateFrom($fields['ends_at']);

        $fields['slug'] = str_slug($fields['name']);

        $event = $organization->events()->find($id);

        if (isset($fields['image'])) {
            $file = $request->file('image')->storePublicly('events');
            $fields['image'] = Storage::url($file);
        }

        if (isset($fields['video'])) {
            $video_file = $request->file('video')->storePublicly('events');
            $fields['video'] = Storage::url($video_file);
        }

        if (request('fb_pixel_id')) {
            $fields['fb_pixel_id'] = request('fb_pixel_id');
        }

        if (request('ga_measure_id')) {
            $fields['ga_measure_id'] = request('ga_measure_id');
        }

        if ($request['statement']) {
            $fields['statement'] = $request['statement'];
        }
        if ($request['prompt']) {
            $fields['prompt'] = $request['prompt'];
        }

        if (request('theatre')) {
            $theatre = Theatre::where('id', request('theatre'))->first();
            if ($theatre && $theatre->id !== $event->theatre_id) {
                $fields['theatre_id'] = $theatre->id;
                $fields['theatre_json'] = $theatre->seat_json;
            }
        } else {
            $fields['theatre_id'] = null;
            $fields['theatre_json'] = null;
        }

        $fields['image'] = isset($fields['image']) ? $fields['image'] : $event->image;

        $event->update($fields);

        return redirect($event->link('events.show'));
    }

    public function insertPdf($request, $id)
    {

        foreach ($request->file('pdf') as $file) {

            $extension = $file->getClientOriginalExtension();

            if ($extension == 'pdf') {
                $filenameWithExt = $file->getClientOriginalName();
                //Get just filename
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                // Get just ext

                // Filename to store

                $fileNameToStore = time() . '.' . $extension;

                $file = $file->storePublicly('events');;

                $pdf = Storage::url($file);

                $eventAttachment = new EventAttachment();
                $eventAttachment->event_id = $id;
                $eventAttachment->name = $filename . '.' . $extension;
                $eventAttachment->file_name = $pdf;
                $eventAttachment->save();
            }
        }
    }

    /*
	* Upload event pdf.
	*/
    public function pdfUpload(Request $request, $id)
    {

        if ($request->file('pdf')) {

            $eventAttachment = EventAttachment::where('event_id', $id)->count();

            //if ($eventAttachment  <= 4) {
            $this->insertPdf($request, $id);

            return json_encode(['sucess' => true]);
            //} else {
            //	return json_encode(['sucess' => false, 'message' => 'You are used 5 pdf upload limit']);
            //}
        }
    }

    /*
	* Event all pdf datatables listing.
	*/
    public function getPurchasers($id)
    {

        $data = EventAttachment::where('event_id', $id)->latest()->get();

        // get tickets of the event
        $tickets = Ticket::where('event_id', $id)->get();
        $ticket_ids = [];
        foreach ($tickets as $ticket) {
            $ticket_ids[] = $ticket->id;
        }

        // get vouchers ( purchased ones ) of the event
        $vouchers = Voucher::whereIn('ticket_id', $ticket_ids)->groupBy('user_id')->get();
        // $voucher_ids = [];

        $purchasers = [];
        $user_ids = [];
        foreach ($vouchers as $voucher) {
            // $voucher_ids[] = $voucher->id;
            $user_ids[] = $voucher->user_id;
            array_push($purchasers, $voucher->user);
        }

        $users = User::whereIn('id', $user_ids)->get();

        return DataTables::of($purchasers)
            ->addColumn('avatar', function ($row) {
                return "<img style='width: 50px;' src=" . $row->avatar . ">";
            })
            ->addColumn('name', function ($row) {
                return $row->name;
            })
            ->addColumn('email', function ($row) {
                return $row->email;
            })
            ->rawColumns(['avatar'])
            ->make(true);
    }

    /*
    * Return Event all pdf datatables listing.
    */
    public function getPdfFiles($id)
    {

        $data = EventAttachment::where('event_id', $id)->latest()->get();

        return DataTables::of($data)
            ->addColumn('name', function ($row) {

                return $row->name;
            })
            ->addColumn('action', function ($row) {

                $view = '<a title="View" class="text-success mr-2" href="' . $row->file_name . '" target="_blank"><i class="fa fa-eye"></i></a>';

                $btn = '<a title="Delete" class="text-success mr-2 delete-record" href="javascript:void(0);" data-url="' . $row->id . '"><i class="fa fa-trash"></i></a>';

                return $view . '|' . $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /*
	* Remove event pdf.
	*/
    public function pdf_remove(Request $request)
    {
        $eventAttachment = EventAttachment::find($request->id);
	if ($eventAttachment && Event::find($eventAttachment->event_id)->organization->users()->first()->id === auth()->user()->id){
            $file = str_replace(env('AWS_URL'), '', $eventAttachment->file_name);
            $exists = Storage::exists($file);
            if ($exists) {
                Storage::delete($eventAttachment->file_name);
                $eventAttachment->delete();
                return json_encode(['sucess' => true]);
            }
        }
        return json_encode(['sucess' => false]);
    }

    /*
	* Remove event video.
	*/
    public function video_remove(Request $request)
    {
        $event = Event::find($request->event_id);
        if ($event && $event->organization->users()->first()->id === auth()->user()->id){
            $file = str_replace(env('AWS_URL'), '', $event->video);
            $exists = Storage::exists($file);
            if ($exists) {
                Storage::delete($event->video);
                $event->video = null;
                $event->save();
                return json_encode(['sucess' => true]);
            }
        }
        return json_encode(['sucess' => false]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        $organization = auth()->user()->organizations()->where('id', $event->organization->id)->firstOrFail();

        $event->delete();

        $tickets = Ticket::where('event_id', '=', $event->id)->get();

        foreach ($tickets as $ticket) {
            $ticket->delete();
        }

        return redirect(route('organization.show', $organization))
            ->with('message', 'Event deleted!');
    }

    /**
     * @param \stdClass $fields
     * @return object
     * @throws ValidationException
     */
    private function validateDates(\stdClass $fields)
    {
        $fields->starts_at = $this->parseDateFrom($fields->starts_at);

        $fields->ends_at = $this->parseDateFrom($fields->ends_at);

        if (Carbon::now()->greaterThan($fields->starts_at)) {
            return back()->withErrors(['starts_at' => 'Event start time must be in the future.']);
        }

        if ($fields->starts_at->greaterThanOrEqualTo($fields->ends_at)) {
            return back()->withErrors(['ends_at' => 'Event end time needs to be after the start time.']);
        }

        return $fields;
    }

    public function sendFeedbackMail(Request $request)
    {
        $usersIds = explode(',', $request->query('pur'));
        $users = User::whereIn('id', $usersIds)->get();
        foreach ($users as $user) {
            $username = $user->name;
            $link = route('event.leave_feedback', ['event' => base64_encode($request->query('event'))]);
            $data = ['name' => $username, 'link' => $link];
            Mail::send('feedback', $data, function ($message) use ($user) {
                $message->to($user->email)->subject('Leave Feedack');
                $message->from('info@athleticjunction.com', 'Events Smarter');
            });
        }
    }

    public function leave_feedback(Request $request)
    {
        $event = $request->query('event');
        if (empty($event)) {
            return redirect('/')
                ->with('message', 'Invalid URL');
        }
        $eventId = base64_decode($event);
        $userId = auth()->user()->id;

        $purchasers = json_decode($this->getPurchasersData($eventId));
        $purcharsId = [];
        foreach ($purchasers as $purchaser) {
            array_push($purcharsId, $purchaser->id);
        }

        if (!in_array($userId, $purcharsId)) {
            return redirect('/')
                ->with('message', 'Only purchasers can leave rating.');
        }

        $feedback = EventFeedback::where(['event_id' => $eventId, 'user_id' => $userId])->latest()->get();
        $event = Event::find($eventId);
        return view('events.feedback', compact('feedback', 'event'));
    }

    public function saveFeedback(Request $request)
    {
        $fields = $this->validate($request, [
            'rating' => 'required',
            'review' => 'required',
            'event_id' => 'required'
        ]);
        $fields['user_id'] = auth()->user()->id;
        EventFeedback::create($fields);
        return back()->with('message', 'Feedback has been saved.');;
    }

    public function eventFeedbacks()
    {
        $event = Event::findOrFail(request('event'));
        $feedbacks = EventFeedback::where('event_id', request('event'))->with(['user'])->paginate(5);
        return view('events.user_feedback', compact('feedbacks', 'event'));
    }

    public function getPurchasersData($id)
    {


        // get tickets of the event
        $tickets = Ticket::where('event_id', $id)->get();
        $ticket_ids = [];
        foreach ($tickets as $ticket) {
            $ticket_ids[] = $ticket->id;
        }

        // get vouchers ( purchased ones ) of the event
        $vouchers = VoucherBundle::whereIn('ticket_id', $ticket_ids)->groupBy('user_id')->get();
        // $voucher_ids = [];

        $purchasers = [];
        $user_ids = [];
        foreach ($vouchers as $voucher) {
            // $voucher_ids[] = $voucher->id;
            $user_ids[] = $voucher->user_id;
            array_push($purchasers, $voucher->user);
        }

        $users = User::whereIn('id', $user_ids)->get();

        return json_encode($users);
    }

    public function getAgoraAccessToken(Ticket $ticket, Request $request)
    {
        if ($request->cookie('event_token')) {
            $event_id = Crypt::decryptString($request->cookie('event_token'));
        }
        if ((auth()->user() && auth()->user()->id !== $ticket->event->organization->users()->first()->id) || (isset($event_id) && (int)$event_id !== $ticket->event->id)) {
            return \json_encode([
                'success' => 0,
                'message' => 'no permission'
            ]);
        } else {
            $appID = env('AGORA_APP_ID');
            $appCertificate = env('AGORA_APP_CERTIFICATE');
            $channelID = substr($ticket->stream_url, strpos($ticket->stream_url, '/live/') + 6);
            $channelName = "live-" . $channelID;
            $role = RtcTokenBuilder::RolePublisher;
            $privilegeExpiredTs = 0;
            $UID = $ticket->event->organization->users()->first()->id;
            $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $UID, $role, $privilegeExpiredTs);

            return \json_encode([
                'success' => 1,
                'message' => 'success',
                'data' => [
                    'token' => $token,
                    'uid' => $UID
                ]
            ]);
        }
    }

    public function inviteCamereCrew(Event $event, Request $request)
    {
        if (auth()->user()->id !== $event->organization->users()->first()->id) {
            return \json_encode([
                'success' => 0,
                'message' => 'no permission'
            ]);
        } else {
            try {
                $eventname = $event->name;
                $name = $event->organization->users()->first()->name;
                $link = route('events.show', [$event->organization, $event->slug, $event]);
                $email = $request['email'];
                $access_token = Crypt::encryptString($event->id);
                Mail::to($email)->send(new \App\Mail\inviteCameraCrewTeam($eventname, $name, $link . '?token=' . $access_token));
                return \json_encode([
                    'success' => 1,
                    'message' => 'success'
                ]);
            } catch (\Throwable $th) {
                return \json_encode([
                    'success' => 0,
                    'message' => $th->getMessage()
                ]);
            }
        }
    }

    public function duplicate(Organization $organization, $id)
    {
        try {
            $event = Event::find($id);

            //event
            $new_event = $event->replicate();
            $new_event->save();

            auth()->user()->events()->attach([$new_event->id]);

            // attachment
            $files = EventAttachment::where('event_id', $id)->latest()->get();
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
                //ticket bundles
                $bundles = $item->bundles()->get();
                $bundles->each(function ($bundle_item, $key) use ($copy) {
                    $copy_bundle = $bundle_item->replicate();
                    $copy_bundle->ticket_id = $copy->id;
                    $copy_bundle->save();
                });
            });

            // banners
            $banners = Banner::where('link_id', $id)->where('link_type', '0')->get();
            $banners->each(function ($item, $key) use ($new_event) {
                $copy = $item->replicate();
                $copy->link_id = $new_event->id;
                $copy->save();
            });
            return response()->json(['success' => 1, 'link' => $new_event->link('events.show')]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 422);
        }
    }
}
