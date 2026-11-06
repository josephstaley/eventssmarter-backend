<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class ProfileController extends Controller {


    public function edit($id) {

        $user = User::findOrFail($id);

        if (auth()->user()->id !== $user->id) {
            return redirect()->back();
        }

        return view('profile.edit', compact('user'));
    }


    public function update(ProfileRequest $profileRequest, $id) {

        $user = User::findOrFail($id);

        if (auth()->user()->id !== $user->id) {
            return redirect()->back();
        }

        $oldAvatar = $user->avatar;
        $avatar = request('avatar');

        if($avatar) {
            $file = $profileRequest->file('avatar')->storePublicly('users');
            $avatar = Storage::url($file);
        }

        $user->update([
            'name' => request('name'),
            'title' => request('title'),
            'email' => request('email'),
            'avatar' => isset($avatar) ? $avatar : $oldAvatar,
            'phone_number' => request('phone_number'),
            'facebook_link' => request('facebook_link'),
            'linkedin_link' => request('linkedin_link'),
            'twitter_link' => request('twitter_link'),
            'website_link' => request('website_link'),
            'instagram_link' => request('instagram_link')
        ]);

        return redirect()->route('home');
    }


    public function updatePassword(Request $request, $id) {

        $this->validate($request, [
            'password' => 'required|min:6|max:16|confirmed',
        ]);

        $user = User::findOrFail($id);

        $user->password = \Hash::make(request()->password);
        $user->save();

        return redirect()->route('home')->with('success', 'Password updated successfully!');
    }

    public function unsubscribe($token){
        $email = Crypt::decryptString($token);
        $user = User::where('email', $email)->get()->first();
        if ($user){
            $user->email_unsubscribed = 1;
            $user->save();
        }
        return view('unsubscribed');
    }

    public function deactivate($id) {
        $user = User::findOrFail($id);
        if (auth()->user()->id !== $user->id) {
            return redirect()->back();
        }
        $user->delete();
        Auth::logout();
        return redirect()->to('login')->with('deactivated_account', 1);
    }

}
