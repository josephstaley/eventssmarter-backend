<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\MyEvents;
use App\Models\Event;
use App\Models\Banner;
use App\Models\Ticket;
use App\Models\Stream;
use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;
use RolfHaug\TokenAuth\LoginToken;
use Illuminate\Support\Facades\Crypt;
use DB;

class HomeController extends Controller
{
    function getQuotes()
    {
        return collect([
            [
                'image' => 'https://randomuser.me/api/portraits/men/5.jpg',
                'name' => 'TERRY JOHNSON',
                'quote' => 'The easy-to-read dashboard lets me see how many people purchased tickets so we can better plan ahead for the approaching events.',
                'subtitle' => 'Event Manager',
            ],
            [
                'image' => 'https://randomuser.me/api/portraits/women/25.jpg',
                'name' => 'BRIANA TERRY',
                'quote' => 'Sometimes I forget to tell my parents about upcoming events I want to attend, but even if it’s the last minute, they can still purchase tickets so I don’t miss out.',
                'subtitle' => 'Student',
            ],
            [
                'image' => 'https://randomuser.me/api/portraits/women/64.jpg',
                'name' => 'SAMANTHA VOSHELL',
                'quote' => 'With our busy schedules oftentimes we forget to plan ahead and get tickets to school events in advance. Now, I can purchase tickets in an instant from the ' . env('APP_NAME') . ' website.',
                'subtitle' => 'Mother of 5',
            ],
            [
                'image' => 'https://randomuser.me/api/portraits/men/92.jpg',
                'name' => 'MIKE CHIN',
                'quote' => 'The QR code scanner makes checking students in fast and efficient plus it prevents huge lines from handling cash!',
                'subtitle' => 'Teacher\'s Assistant',
            ],
        ]);
    }

    function welcome()
    {
        $upcomming_events = Event::where('ends_at', '>=', Carbon::now())->where('events.is_private', 0)->whereHas('organization')->orderBy('ends_at')->limit(9)->get();
        $liveStreamTickets = Ticket::join('events', 'tickets.event_id', 'events.id')->select('tickets.*', 'events.ends_at')->whereNull('events.deleted_at')->where('is_live_stream', 1)->where('tickets.is_private', 0)->where('events.is_private', 0)->where('events.ends_at', '>=', Carbon::now())->orderBy('events.ends_at')->limit(5)->get();
        $login_token = '';
        if (auth()->user()) {
            $login_token = LoginToken::generate(auth()->user(), true);
        }
        return view('welcome')
            ->with('upcomming_events', $upcomming_events)
            ->with('homepage', true)
            ->with('quotes', $this->getQuotes())
            ->with('login_token', $login_token)
            ->with('noShop', true)
            ->with('streams', $liveStreamTickets);
    }

    function privacy()
    {
        return view('privacy');
    }

    function privacy_eu()
    {
        if (session()->has('locale') && session()->get('locale') == 'en') {
            return redirect()->to('privacy');
        }
        return view('privacy-eu-gdpr');
    }

    function terms()
    {
        return view('terms');
    }

    function contact()
    {
        return view('contact');
    }

    public function index(MyEvents $myEvents)
    {
        // dd(url('/tickets?').LoginToken::generate(\App\User::where('id', 5887)->first(), true));
        $dateTime = null;
        if (!$dateTime instanceof \DateTime) {
            $dateTime = Carbon::parse($dateTime);
        }
        $myEvents = $myEvents->with('organization')->for(auth()->user())->paginate();

        $pastEvents = auth()->user()->events()->with('organization')->where('starts_at', '<', $dateTime)->orderBy('starts_at', 'desc')->get()->filter(function ($item) {
            return $item->organization;
        });

        // $refOrgs = auth()->user()->organizations;
        $referral_revenue_array = [];
        $total_revenue_amount = 0;
        for ($i = 0; $i < 12; $i++) {
            $total_monthly_amount = DB::table('voucher_bundles as main')->leftJoin('tickets as t', 't.id', '=', 'main.ticket_id')->leftJoin('events as e', 'e.id', '=', 't.event_id')->leftJoin('organizations as mo', 'mo.id', '=', 'e.organization_id')->leftJoin('organizations as ro', 'ro.slug', '=', 'mo.referral_id')->leftJoin('organization_user as ou', 'ou.organization_id', '=', 'ro.id')->where('ou.user_id', auth()->user()->id)->where('main.refunded_at', '=', null)->where('main.deleted_at', '=', null)->where('main.created_at', '>', Carbon::now()->subMonth($i)->startOfMonth())->where('main.created_at', '<', Carbon::now()->subMonth($i)->endOfMonth())->value(DB::raw("SUM(((((main.detail_amount + 73.5) * 1.0282 / 0.91679 - 25)*mo.app_fee_percentage/100) + 50) * mo.referral_fee_percentage / 10000)"));
            $referral_revenue_array[date('F', strtotime(Carbon::now()->subMonth($i)))] = $total_monthly_amount ? '$ ' . number_format($total_monthly_amount, 2) : 0;
            $total_revenue_amount += $total_monthly_amount;
        }
        $total_revenue_amount = '$ ' . number_format($total_revenue_amount, 2);

        $organizations = auth()->user()->organizations()->with('events')->orderBy('created_at', 'desc')->get();

        $follows = auth()->user()->follows()->with('events')->paginate();

        $user = auth()->user();

        $streams = $user->streams()->orderBy('updated_at', 'desc')->get();
        $meetings = $user->meetings()->orderBy('updated_at', 'desc')->get();
        $login_token = LoginToken::generate(auth()->user(), true);
        $encrypted_login_token = Crypt::encryptString($login_token);

        $subscriptions = Subscription::where('user_id', $user->id)->leftJoin('organizations', 'organizations.id', 'organization_id')->whereNull('organizations.deleted_at')->get();

        return view('home', compact('organizations', 'user',  'follows', 'myEvents', 'pastEvents', 'streams', 'meetings', 'login_token', 'encrypted_login_token', 'subscriptions', 'referral_revenue_array', 'total_revenue_amount'));
    }

    function getCustomers(Request $request)
    {
        $user = auth()->user();

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
        $totalRecords = count(Customer::where('user_id', $user->id)
            ->groupBy(['email', 'phone_number'])
            ->select('email', DB::raw('count(*) as total'))
            ->get());
        $totalRecordswithFilter = count(Customer::where('user_id', $user->id)
            ->groupBy(['email', 'phone_number'])
            ->select('email', DB::raw('count(*) as total'))
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->where('lname', 'like', '%' . $searchValue . '%')
                    ->where('fname', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone_number', 'like', '%' . $searchValue . '%');
            })->get());
        // Fetch records
        $records = Customer::where('user_id', $user->id)
            ->groupBy(['email', 'phone_number'])
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->where('lname', 'like', '%' . $searchValue . '%')
                    ->where('fname', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('phone_number', 'like', '%' . $searchValue . '%');
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

    function about()
    {
        $content = Content::wherePage('about')->first();

        return view('about')
            ->with('content', $content)
            ->with('quotes', $this->getQuotes()->shuffle(1000001));
    }

    function free_events()
    {
        return view('free-events')
            ->with('quotes', $this->getQuotes()->shuffle(1000001));
    }

    function why()
    {
        $content = Content::wherePage('why')->first();

        return view('why')
            ->with('content', $content)
            ->with('quotes', $this->getQuotes()->shuffle(1000001));
    }

    function search()
    {
        return redirect()->to('/events/' . request('category') . '?' . http_build_query(request()->only('query', 'category', 'city')));
    }

    function recoverOrgPassword()
    {
        return redirect()->to('/login');
    }
    public function banner_click(Request $request)
    {
        $id = $request->id;
        try {
            $model = Banner::where('id', $id)->first();
            $count = $model->click_counts;
            $count++;
            $model->update(['click_counts' => $count]);
        } catch (\Throwable $th) {
        }
    }

    public function livestream_guide()
    {
        return view('live-stream-guide');
    }

    public function bestAlternativeLeadingCompetitor()
    {
        return view('best-alternative-leading-competitor');
    }
}
