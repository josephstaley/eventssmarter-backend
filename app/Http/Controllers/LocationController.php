<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function edit(Event $event)
    {
        return view('location.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $address = app('geocoder')->geocode($request->autocomplete)->get()->first();
        try {
            $fields = [
                'location_name' => $request->location_name,
                'zip' => $address->getPostalCode(),
                'state' => count($address->getAdminLevels()) > 0 ? $address->getAdminLevels()->first()->getName() : null,
                'city' => $address->getLocality(),
                'street' => $address->getStreetName(),
                'street_number' => $address->getStreetNumber(),
                'lat' => $address->getCoordinates()->getLatitude(),
                'lng' => $address->getCoordinates()->getLongitude(),
                'address' => $request->autocomplete,
            ];

            $event->update($fields);

            return redirect()->route('events.show', [$event->organization, $event->slug, $event->id]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function destroy($id)
    {
        //
    }
}
