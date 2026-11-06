<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use App\Models\Organization;
use App\Models\Event;
use App\Models\User;
use App\Models\VoucherBundle;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    function user(Request $request)
    {
        return $request->user();
    }

    function search()
    {

        $query = request('query');

        $organizations = Organization::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->orWhere('state', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function($org){
                $org->upcoming = $org->events()->upcoming()->count();
                return $org;
            })
        ;

        $events = Event::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('city', 'like', "%{$query}%")
            ->orWhere('state', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->upcoming()
            ->with('organization')
            ->limit(5)
            ->get();

        return compact('organizations', 'events');
    }

    function email()
    {
        return [
            'exists' => User::where('email', request('query'))->limit(1)->get()->count() > 0
        ];
    }

    function login()
    {

        $password = request('password');

        $organization = Organization::where('password', $password)->first();

        if (!$organization) return ['success' => false, 'msg' => 'Invalid password, please try again.'];

        // change this once apple/android approval
        //$events = $organization->events()->whereBetween('starts_at', [Carbon::now()->startOfDay(), Carbon::now()->addHours(6)])->get();
        $events = $organization->events()->orderBy('starts_at', 'desc')->get()->map(function ($e) {
            $e->checkedIn = $e->vouchers()->where('used_at', '!=', null)->where('refunded_at', null)->count();
            $e->checkedAvailable = $e->vouchers()->count();
            return $e;
        });

        return ['success' => !is_null($organization), 'events' => $events, 'organization' => $organization];
    }

    function userlogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return ['success' => true];
        }
        else return ['success' => false, 'msg' => 'Invalid password, please try again.'];
    }

    function events(User $user)
    {
        return $user->upcomingEvents->with('organization')->get();
    }

    function event(Event $event)
    {

        $voucher = VoucherBundle::where('code', request('payload'))
            ->with('user', 'ticket')
            ->first();

        if (!$voucher) {
            return ['success' => false];
        }

        // if ($voucher->used_at) {
        //     return ['success' => true, 'used' => true, 'voucher' => $voucher];
        // }

        if ($event->id == $voucher->ticket()->withTrashed()->first()->event->id) {
            $voucher->update(['used_at' => \Carbon\Carbon::now()]);
            return ['success' => true, 'used' => false, 'voucher' => $voucher];
        }

        return ['success' => false];

    }

}
