<?php

namespace App\Http\Controllers\API\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserAPIController extends Controller
{

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
                return $this->sendError(__('auth.failed'), 401);
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 401);
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
}
