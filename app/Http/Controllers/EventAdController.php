<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventAdController extends Controller
{

    public function edit ($id) {

        $event = Event::where('id', $id)->first();

        if (!$event) {
            return redirect()->back();
        }

        return view('events.ad.form', compact('event'));
    }


    public function store(Request $request, $id) {

        $this->validate($request, [
            'ad_image' => 'image|nullable',
            'ad_link' => 'url|nullable',
        ]);

        $event = Event::where('id', $id)->first();

        $image = request('ad_image');

        if ($image) {
            $file = $request->file('ad_image')->storePublicly('event/ad');
            $image = Storage::url($file);
        }

        $event->update([
            'ad_image' => isset($image) ? $image : $event->ad_image,
            'ad_link' => request('ad_link')
        ]);

        $organization = Organization::where('id', $event->organization_id)->first();

        return redirect()->route('events.show', [$organization, $event->slug, $event]);
    }

}
