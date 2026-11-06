<?php

namespace App\Http\Controllers\LiveStream;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use RolfHaug\TokenAuth\LoginToken;
use Illuminate\Support\Facades\Crypt;

use App\Classes\AgoraDynamicKey\RtcTokenBuilder;

use App\Models\Ticket;

class LiveStreamAPIController extends Controller
{
    function liveStreamInit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'room_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            if (LoginToken::authenticate($data['token'])){
                $ticket = Ticket::where('id', $data['room_id'])->first();
                $ticket->event->theatre_json = null;
                $organization_owner_id = $ticket->event->organization->users()->first()->id;
                if (!$ticket || $organization_owner_id !== auth()->user()->id){
                    return response()->json([
                        'success' => 0,
                        'message' => 'Ticket ID is invalid'
                    ], 422);
                }
                $appID = env('AGORA_APP_ID');
                $appCertificate = env('AGORA_APP_CERTIFICATE');
                $channelID = substr($ticket->stream_url, strpos($ticket->stream_url, '/live/') + 6 );
                $channelName = "live-".$channelID;
                $role = RtcTokenBuilder::RolePublisher;
                $privilegeExpiredTs = 0; // 24 hours valid
                $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, auth()->user()->id, $role, $privilegeExpiredTs);

                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => auth()->user(),
                        'ticket' => $ticket,
                        'host_id' => $organization_owner_id,
                        'token' => $token
                    ]
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ]);
        }
        return response()->json([
            'success' => 0,
            'message' => 'token is invalid'
        ], 422);
    }

    function liveStreamInvite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'room_id' => 'required',
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            $token = Crypt::decryptString($request['token']);
            $token = substr($token, 14);
            if (LoginToken::authenticate($token)){
                $ticket = Ticket::where('id', $data['room_id'])->first();
                $ticket->event->theatre_json = null;
                $organization_owner_id = $ticket->event->organization->users()->first()->id;
                if (!$ticket || $organization_owner_id !== auth()->user()->id){
                    return response()->json([
                        'success' => 0,
                        'message' => 'Ticket ID is invalid'
                    ], 422);
                }
                $appID = env('AGORA_APP_ID');
                $appCertificate = env('AGORA_APP_CERTIFICATE');
                $channelID = substr($ticket->stream_url, strpos($ticket->stream_url, '/live/') + 6 );
                $channelName = "live-".$channelID;
                $user = [
                    "name" => $request['name'],
                    "avatar" => "https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png",
                    "id" => time()
                ];
                $role = RtcTokenBuilder::RolePublisher;
                $privilegeExpiredTs = 0;
                $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $user['id'], $role, $privilegeExpiredTs);

                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => $user,
                        'ticket' => $ticket,
                        'host_id' => $organization_owner_id,
                        'token' => $token
                    ]
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ]);
        }
        return response()->json([
            'success' => 0,
            'message' => 'token is invalid'
        ], 422);
    }
}
