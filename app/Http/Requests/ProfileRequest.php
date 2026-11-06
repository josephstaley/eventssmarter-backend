<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = User::find($this->id);

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'image|max:2000',
            'facebook_link' => 'url|nullable',
            'linkedin_link' => 'url|nullable',
            'twitter_link' => 'url|nullable',
            'website_link' => 'url|nullable',
            'instagram_link ' => 'url|nullable'
        ];
    }
}
