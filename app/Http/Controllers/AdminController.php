<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Models\VoucherBundle;
use Carbon\Carbon;
use App\Models\Organization;
use App\Models\Banner;
use App\Models\DeletedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ResendTicketLink;

use \PDF;
use DB;

class AdminController extends Controller
{
    function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $thisWeek = [Carbon::now()->subWeek(), Carbon::now()];
        $lastWeek = [Carbon::now()->subWeek(2), Carbon::now()->subWeek()];

        $salesThis = VoucherBundle::whereBetween('created_at', $thisWeek)->where('refunded_at', null)->sum('detail_amount');
        $salesLast = VoucherBundle::whereBetween('created_at', $lastWeek)->where('refunded_at', null)->sum('detail_amount');

        $refundsThis = VoucherBundle::whereBetween('created_at', $thisWeek)->where('refunded_at', '!=', null)->sum('detail_amount');
        $refundsLast = VoucherBundle::whereBetween('created_at', $lastWeek)->where('refunded_at', '!=', null)->sum('detail_amount');

        $salesThis = number_format($salesThis / 100, 2);
        $salesLast = number_format($salesLast / 100, 2);

        $refundsThis = number_format($refundsThis / 100, 2);
        $refundsLast = number_format($refundsLast / 100, 2);
        $banner_click = Banner::get()->sum('click_counts');

        return view('admin.index', compact('salesThis', 'salesLast', 'refundsThis', 'refundsLast', 'banner_click'))

            ->with('usersThis', User::whereBetween('created_at', $thisWeek)->paginate())
            ->with('organizationsThis', Organization::whereBetween('created_at', $thisWeek)->paginate())
            ->with('eventsThis', Event::whereBetween('created_at', $thisWeek)->paginate())

            ->with('usersLast', User::whereBetween('created_at', $lastWeek)->paginate())
            ->with('organizationsLast', Organization::whereBetween('created_at', $lastWeek)->paginate())
            ->with('eventsLast', Event::whereBetween('created_at', $lastWeek)->paginate())

            ->with('noShop', true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($model)
    {
        $name = ucwords($model);

        $model = '\App\\Models\\' . ucwords($model);
        $model = $model::orderBy('created_at', 'desc')->paginate();

        return view('admin.show')
            ->with('model', $model)
            ->with('name', $name)
            ->with('noShop', true);
    }

    public function userIndex(Request $request)
    {
        return view('admin.users')
            ->with('name', 'Users')
            ->with('noShop', true);
    }

    public function sendTicketLinkToken(Request $request)
    {
        if ($request->get('user_id')) {
            $user = User::where('id', $request->get('user_id'))->first();
            if ($user) {
                $user->notify(new ResendTicketLink($user));
                return ['success' => true];
            }
            return ['success' => false, 'message' => 'User Not found'];
        }
        return ['success' => false, 'message' => 'user_id is missing'];
    }

    public function getUsers(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = User::select('count(*) as allcount')->count();
        $totalRecordswithFilter = count(User::select(DB::raw('count(*) as total'))
            ->LeftJoin('voucher_bundles', 'voucher_bundles.user_id', 'users.id')
            ->where('name', 'like', '%' . $searchValue . '%')
            ->orWhere('email', 'like', '%' . $searchValue . '%')
            ->orWhere('phone_number', 'like', '%' . $searchValue . '%')
            ->orWhere('charge_id', 'like', '%' . $searchValue . '%')
            ->groupBy('users.id')->get());

        // Fetch records
        $records = User::orderBy($columnName, $columnSortOrder)
            ->select('users.*')
            ->LeftJoin('voucher_bundles', 'voucher_bundles.user_id', 'users.id')
            ->where('users.name', 'like', '%' . $searchValue . '%')
            ->orWhere('users.email', 'like', '%' . $searchValue . '%')
            ->orWhere('users.phone_number', 'like', '%' . $searchValue . '%')
            ->orWhere('charge_id', 'like', '%' . $searchValue . '%')
            ->groupBy('users.id')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $email = $record->email;
            $phone_number = $record->phone_number;
            $created_at = (new Carbon($record->created_at))->toDateTimeString();

            $data_arr[] = array(
                "id" => $id,
                "avatar" => $record->avatar ? $record->avatar : 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png',
                "name" => $name,
                "email" => $email,
                "phone_number" => $phone_number,
                "created_at" => $created_at,
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

    public function getUser(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'success',
            'data' => $user
        ]);
    }

    public function signUser(User $user)
    {
        try {
            auth()->login($user);
            return response()->json([
                'success' => true,
                'message' => 'success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function deleteUser(User $user)
    {
        try {
            DeletedUser::forceCreate($user->toArray());
            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function updateUser(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required'
        ]);
        try {
            $user->name = $request->get('name');
            $user->email = $request->get('email');
            if ($request->get('phone_number') && trim($request->get('phone_number')) !== '') {
                $user->phone_number = $request->get('phone_number');
            }
            if ($request->get('facebook_link') && trim($request->get('facebook_link')) !== '') {
                $user->facebook_link = $request->get('facebook_link');
            }
            if ($request->get('twitter_link') && trim($request->get('twitter_link')) !== '') {
                $user->twitter_link = $request->get('twitter_link');
            }
            if ($request->get('linkedin_link') && trim($request->get('linkedin_link')) !== '') {
                $user->linkedin_link = $request->get('linkedin_link');
            }
            if ($request->get('website_link') && trim($request->get('website_link')) !== '') {
                $user->website_link = $request->get('website_link');
            }
            if ($request->get('instagram_link') && trim($request->get('instagram_link')) !== '') {
                $user->instagram_link = $request->get('instagram_link');
            }
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'success'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($model, $id)
    {
        $name = ucwords($model);

        $model = '\App\\Models\\' . ucwords($model);
        $model = $model::find($id);

        return view('admin.edit')
            ->with('model', $model)
            ->with('name', $name)
            ->with('noShop', true);
    }

    public function eventExport($id)
    {
        $event = Event::where('id', $id)->firstOrFail();

        // application fee: fee percent + 10 cents, stripe fee: 2.9% + 30 cents.  this value is for calculating fee counts
        $voucher_fee_count = VoucherBundle::join('tickets', 'tickets.id', '=', 'voucher_bundles.ticket_id')->where('tickets.event_id', $event->id)->where('payment_method', 'stripe')->where('detail_amount', '>', 0)->groupBy('upgrade_charge_id', 'charge_id')->get()->count();
        $org_sent_amount = VoucherBundle::join('tickets', 'tickets.id', '=', 'voucher_bundles.ticket_id')->where('tickets.event_id', $event->id)->where('payment_method', 'stripe')->where('detail_amount', '>', 0)->sum('detail_amount');
        $volume = $event->organization->currencySymbol . ' '  . number_format((($org_sent_amount + $voucher_fee_count * 10) / (1 - $event->organization->app_fee_percentage * .01) / 0.94 + $voucher_fee_count * 30) / .971 / 100, 2);
        $reserved_volume = $event->organization->currencySymbol . ' '  . number_format((($org_sent_amount + $voucher_fee_count * 10) / (1 - $event->organization->app_fee_percentage * .01) / 0.94 - $org_sent_amount) / 100, 2);
        $data = [
            'name' => $event->name,
            'url' => $event->link('events.show'),
            'start_date' => $event->starts_at->format('l M j Y') . ', ' . $event->starts_at->format('g:ia'),
            'end_date' => $event->ends_at->format('l M j Y') . ', ' . $event->ends_at->format('g:ia'),
            'account_id' => $event->organization->stripe_account,
            'volume' => $volume,
            'vouchers' => VoucherBundle::join('tickets', 'tickets.id', '=', 'voucher_bundles.ticket_id')->where('tickets.event_id', $event->id)->where('payment_method', 'stripe')->where('detail_amount', '>', 0)->groupBy('upgrade_charge_id', 'charge_id')->selectRaw('*, sum(detail_amount) as each_sum_amount')->get(),
            'currency' => $event->organization->currencySymbol,
            'app_fee_percentage' => $event->organization->app_fee_percentage,
            'reserved_volume' => $reserved_volume,
        ];
        $pdf = PDF::loadView('pdf.eventInfo', $data);

        return $pdf->setPaper('a4', 'landscape')
            ->setWarnings(false)
            ->download($event->name . ' Information.pdf');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $model, $id)
    {
        $model = '\App\\Models\\' . ucwords($model);
        $model = $model::find($id);

        abort_unless($model->forceFill($request->all())->save(), 500, 'Unable to save data');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($model, $id)
    {
        $name = $model;
        if ($name === 'organization') {
            Event::where('organization_id', $id)->delete();
        }
        $model = '\App\\Models\\' . ucwords($model);
        $model = $model::find($id);

        $model->delete();

        return redirect()->to('/admin/' . $name);
    }
    public function banner_show()
    {
        $name = ucwords('banner');
        $banner = '\App\\Models\\' . ucwords('banner');
        $banner = $banner::paginate();
        $banner->setPath('');
        return view('admin.banner_show')
            ->with('banner', $banner)
            ->with('noShop', true);
    }
    public function banner_create()
    {
        $events = Event::get();
        $organizations = Organization::get();
        return view('admin.banner_create')
            ->with('events', $events)
            ->with('organizations', $organizations);
    }
    public function banner_store(Request $request)
    {

        $fields = $this->validate($request, [
            'name' => 'required',
            'image' => 'required|image',
            'link_url' => 'required',
            'link_type' => 'required',
            'link_id' => 'required',
        ]);

        $file = $request->file('image')->storePublicly('banners');

        $fields['image'] = Storage::url($file);

        $banner = Banner::create($fields);

        return redirect()->route('Admin.banner_show', $banner);
    }

    public function banner_view($id)
    {
        $model = 'banner';
        $model = '\App\\Models\\' . ucwords($model);
        $model = $model::find($id);
        return view('admin.banner_view')
            ->with('banner', $model)
            ->with('noShop', true);
    }
    public function banner_destory($id)
    {

        $banner = Banner::where('id', $id)->firstOrFail();
        $banner->delete();
        return redirect(route('Admin.banner_show', $banner))
            ->with('message', 'Banner deleted!');
    }

    public function eventIndex(Request $request)
    {
        return view('admin.events')
            ->with('name', 'Events')
            ->with('noShop', true);
    }

    public function getEvents(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = Event::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Event::select(
            'count(*) as allcount',
            'organizations.name as org_name',
            'events.created_at as event_created_at',
            'events.starts_at as event_starts_at',
            'events.name as event_name'
        )
            ->join('organizations', 'events.organization_id', '=', 'organizations.id')
            ->where(function ($query) use ($searchValue) {
                $query->where('events.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('organizations.name', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records
        $records = Event::orderBy($columnName, $columnSortOrder)
            ->join('organizations', 'events.organization_id', '=', 'organizations.id')
            ->where(function ($query) use ($searchValue) {
                $query->where('events.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('organizations.name', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->select('*', 'organizations.name as org_name', 'events.created_at as event_created_at', 'events.starts_at as event_starts_at', 'events.name as event_name', 'events.slug as event_slug', 'events.id as event_id')
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $id = $record->id;
            $name = $record->name;
            $email = $record->email;
            $created_at = (new Carbon($record->created_at))->toDateTimeString();

            $data_arr[] = array(
                "event_id" => $record->event_id,
                "event_name" => $record->event_name,
                "event_link" => $record->link('events.show', [], $record->event_slug, $record->event_id),
                "org_name" => $record->org_name,
                "org_link" => route('organization.show', $record->organization),
                "event_created_at" => $record->event_created_at,
                "event_starts_at" => $record->event_starts_at,
                'event_export_link' => route('event.adminExportForStripe', $record->event_id)
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
}
