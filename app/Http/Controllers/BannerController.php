<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Helpers;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{

    /**
     * Show the form for creating a new banner.
     *
     * @param $organization
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function create($organization, $title, Event $event)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");

        $banner = new Banner;

        return view('banners.create', compact('organization', 'event', 'banner'));
    }


    public function edit($organization, $id)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");

        $banner = Banner::where('id', '=', $id)->first();

        return view('banners.edit', compact('banner', 'organization'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $organization, $title, Event $event)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");

        $fields = $this->validate($request, [
            'name' => 'required',
            'image' => 'required|image',
            'link_url' => 'required'
        ]);

        $file = $request->file('image')->storePublicly('banners');

        $fields['image'] = Storage::url($file);
        $fields['link_type'] = 0;
        $fields['link_id'] = $event->id;
        if($request['main-banner-checkbox'] == 'on') {
            $fields['is_main_banner'] = 1;
        }
        if (strpos($fields['link_url'], 'http') === false && strpos($fields['link_url'], 'https') === false){
            $fields['link_url'] = 'https://'.$fields['link_url'];
        }

        Banner::create($fields);

        return redirect($event->link('events.show'))->with('message', 'Banner has been created successfully');
    }


    /**
     * @param Request $request
     * @param $organization
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $organization, $id)
    {
        $organization = auth()->user()->organizations()->where('slug', $organization)->firstOrFail();

        abort_unless(Helpers::can('update', $organization), 403, "You don't have permission to view this page.");
        $banner = Banner::where('id', '=', $id)->first();
        $fields = $this->validate($request, [
            'name' => 'required',
            'image' => 'nullable|image',
            'link_url' => 'required'
        ]);
        if($request['main-banner-checkbox'] == 'on') {
            $fields['is_main_banner'] = 1;
        } else {
            $fields['is_main_banner'] = null;
        }

        $modelMainBanner = Banner::where('link_id', $banner->link_id)->where('link_type', '0')->whereNotNull('is_main_banner')->first();

        if($modelMainBanner && $modelMainBanner->id !== $id && $fields['is_main_banner'] === 1) {
            return back()->withErrors(['main-banner-checkbox' => 'Main banner has been already created. Please edit the main banner or make it false!']);
        }

        if (!empty($request->file('image'))) {
            $file = $request->file('image')->storePublicly('banners');

            $fields['image'] = Storage::url($file);
        }
        if (strpos($fields['link_url'], 'http') === false && strpos($fields['link_url'], 'https') === false){
            $fields['link_url'] = 'https://'.$fields['link_url'];
        }
        $banner->update($fields);
        return redirect($banner->event->link('events.show'))->with('message', 'Banner has been updated successfully');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);

        //abort_unless(Helpers::can('update', $banner), 403, "You don't have permission to view this page.");

        $banner->delete();

        return redirect($banner->event->link('events.show'));
    }
}
