<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Event;
use App\Models\Banner;
use App\Models\EventMember;

class BannerAPIController extends Controller
{
    function getOrganizationBanners(Organization $organization)
    {
        $banners = Banner::where('link_id', $organization->id)->get();
        return $this->sendResponse($banners, 'success');
    }

    function deleteOrganizationBanner(Organization $organization, Banner $banner)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization) || $banner->link_id !== $organization->id) {
            return $this->sendError('No Permission', 401);
        }
        $banner->delete();
        return $this->sendResponse(null, 'success');
    }

    function addOrganizationBanner(Organization $organization, Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $organization)) {
            return $this->sendError('No Permission', 401);
        }
        $payload = $this->validate($request, [
            'is_main' => 'required',
            'image_url' => 'required',
            'link_url' => 'required'
        ]);
        if ($payload['is_main']) {
            // check if main banner exist, if exist, delete it.
            $main_banner = Banner::where('link_id', $organization->id)->where('link_type', 1)->where('is_main_banner', 1)->first();
            if ($main_banner) {
                $main_banner->delete();
            }
        }
        $scheme = parse_url($payload['link_url'], PHP_URL_SCHEME);
        if (empty($scheme))
            $payload['link_url'] = 'https://' . ltrim($payload['link_url'], '/');
        $banner = Banner::create([
            'name' => time(),
            'link_url' => $payload['link_url'],
            'link_type' => 1, // link_type: 1:organization, 0:event
            'link_id' => $organization->id,
            'image' => $payload['image_url'],
            'is_main_banner' => $payload['is_main'] ? 1 : null
        ]);
        return $this->sendResponse($banner, 'success');
    }

    function getEventBanners(Event $event)
    {
        $banners = Banner::where('link_id', $event->id)->get();
        return $this->sendResponse($banners, 'success');
    }

    function addEventBanner(Event $event, Request $request)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("No Permission1", 401);
            }
        }
        $payload = $this->validate($request, [
            'is_main' => 'required',
            'image_url' => 'required',
            'link_url' => 'required'
        ]);
        if ($payload['is_main']) {
            // check if main banner exist, if exist, delete it.
            $main_banner = Banner::where('link_id', $event->id)->where('link_type', 0)->where('is_main_banner', 1)->first();
            if ($main_banner) {
                $main_banner->delete();
            }
        }
        $scheme = parse_url($payload['link_url'], PHP_URL_SCHEME);
        if (empty($scheme))
            $payload['link_url'] = 'https://' . ltrim($payload['link_url'], '/');
        $banner = Banner::create([
            'name' => time(),
            'link_url' => $payload['link_url'],
            'link_type' => 0, // link_type: 1:organization, 0:event
            'link_id' => $event->id,
            'image' => $payload['image_url'],
            'is_main_banner' => isset($payload['is_main']) && $payload['is_main'] ? 1 : null
        ]);
        return $this->sendResponse($banner, 'success');
    }

    function deleteEventBanner(Event $event, Banner $banner)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        if (!$user->can('update', $event)) {
            $member = EventMember::where('event_id', $event->id)->where('invited_user_email', $user->email)->where('invitation_accept_status', 'accepted')->where('user_role', 'admin')->first();
            if (!$member) {
                return $this->sendError("No Permission1", 401);
            }
            if ($banner->link_id !== $event->id) {
                return $this->sendError("No Permission", 401);
            }
        }
        $banner->delete();
        return $this->sendResponse(null, 'success');
    }
}
