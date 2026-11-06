<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationAdController extends Controller
{

    public function create($organization) {
        return view('organization.ad.form', compact('organization'));
    }


    public function edit ($id) {

        $organization = Organization::where('id', $id)->first();

        if (!$organization) {
            return redirect()->back();
        }

        return view('organization.ad.form', compact('organization'));
    }


    public function store(Request $request, $id) {

        $this->validate($request, [
            'ad_image' => 'image|nullable',
            'ad_link' => 'url|nullable',
        ]);

        $organization = Organization::where('id', $id)->first();

        $image = request('ad_image');

        if ($image) {
            $file = $request->file('ad_image')->storePublicly('org/ad');
            $image = Storage::url($file);
        }

        $organization->update([
            'ad_image' => isset($image) ? $image : $organization->ad_image,
            'ad_link' => request('ad_link')
        ]);

        return redirect()->route('organization.show', $organization);
    }

}
