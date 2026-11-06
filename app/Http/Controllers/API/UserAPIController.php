<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Notifications\PasswordResetMail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use App\Notifications\Subscribed;
use App\Helper\Helpers as Helper;

class UserAPIController extends Controller
{

    function healthCheck()
    {
        try {
            return $this->sendResponse(env('APP_NAME'), 'All are okay!');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 401);
        }
    }

    function sign_in(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                /** @var \App\Models\User */
                $user = auth()->user();
                $token = $user->createToken('auth_token')->plainTextToken;
                $user->api_token = $token;
                $user->save();
                return $this->sendResponse($user, 'User retrieved successfully');
            } else {
                return $this->sendError(__('auth.failed'), 403);
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
    }

    function sign_up(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone_number' => 'required',
                'password' => [
                    'required',
                    'min:8',
                ],
                'referral_code' => 'nullable',
            ]);

            $user = User::create([
                'name' => $request['name'],
                'fname' => explode(' ', $request->name) ? explode(' ', $request->name)[0] : $request->name,
                'lname' => count(explode(' ', $request->name)) > 1 ? explode(' ', $request->name)[1] : $request->name,
                'email' => $request['email'],
                'phone_number' => $request['phone_number'],
                'password' => bcrypt($request['password']),
                'referral_code' => $request['referral_code'],
            ]);
            return $this->sendResponse($user, 'User created successfully');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 403);
        }
    }

    function sign_out(Request $request)
    {
        $user = User::where('api_token', $request->bearerToken())->first();
        if (!$user) {
            return $this->sendError('User not found', 401);
        }
        try {
            auth()->logout();
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
        }
        return $this->sendResponse($user['name'], 'User logout successfully');
    }

    function forgot_password(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);
        $user = User::where('email', $request->email)->first();
        //Check if the user exists
        if ($user) {
            //Delete Old Tokens
            DB::table('password_resets')->where('email', $user->email)->delete();
            //Create Password Reset Token
            $token = str_random(60);
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
            try {
                Notification::send($user, new PasswordResetMail($token, $user->email, $user->name));
                return $this->sendResponse(null, 'success');
            } catch (\Throwable $th) {
                return $this->sendError($th->getMessage(), 401);
            }
        }
    }

    function reset_password(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
            'token' => 'required',
        ]);
        try {
            $email = strtolower($request->email);
            $password = $request->password;
            $tokenData = DB::table('password_resets')->where('token', $request->token)->first();
            if (!$tokenData)
                return $this->sendError('Token is invalid or expired!', 401);
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user || strtolower($tokenData->email) !== $email)
                return $this->sendError('Email is invalid', 401);
            $user->password = Hash::make($password);
            $user->save();
            DB::table('password_resets')->where('email', $user->email)->delete();
            return $this->sendResponse(null, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 401);
        }
    }

    function updateProfile(Request $request)
    {
        $fields = $this->validate($request, [
            'email' => 'required',
            'avatar' => 'required',
            'name' => 'required',
            'phone_number' => 'nullable',
            'facebook_link' => 'nullable',
            'twitter_link' => 'nullable',
            'website_link' => 'nullable',
            'linkedin_link' => 'nullable',
            'instagram_link' => 'nullable'
        ]);
        /** @var \App\Models\User */
        $user = auth()->user();
        if ($user->email !== $request->email) {
            if (User::where('email', $request->email)->first()) {
                return $this->sendError('Email is already registered!', 422);
            }
        }
        $user->update($fields);
        return $this->sendResponse(null, 'success');
    }

    function updatePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
            ]
        ]);
        /** @var \App\Models\User */
        $user = auth()->user();
        if (Hash::check($request->password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return $this->sendResponse(null, 'success');
        }
        return $this->sendError('Your current password is incorrect!', 422);
    }

    public function subscribe(Request $request, Organization $organization)
    {
        if (!auth()->user()) {
            return redirect()->back();
        }
        $this->validate($request, [
            'subscription_mode' => 'required',
            'event_link' => 'required'
        ]);
        /** @var \App\Models\User */
        $user = auth()->user();
        $user->cancelSubscription($organization->id);
        $priceId = $request['subscription_mode'] === 'month' ? $organization->monthly_subscription_price_id : $organization->yearly_subscription_price_id;
        if ($priceId == 'free-plan') {
            Subscription::create([
                'user_id' => auth()->user()->id,
                'organization_id' => $organization->id,
                'subscription_id' => 'free-plan',
            ]);
            $user_email = auth()->user()->email;
            Notification::route('mail', $user_email)->notify(new Subscribed($organization));
            return $this->sendResponse(null, 'success');
        } else {
            $subscription_link = Helper::createSubscriptionOnPaypal($priceId, $request['event_link']);
            return $this->sendResponse($subscription_link, 'success');
        }
    }

    public function unsubscribe($organization_id)
    {
        try {
            if (!auth()->user()) {
                return redirect()->back();
            }
            /** @var \App\Models\User */
            $user = auth()->user();
            $organization = Organization::where('id', $organization_id)->first();
            $user->cancelSubscription($organization->id);
            return $this->sendResponse(null, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 401);
        }
    }
}
