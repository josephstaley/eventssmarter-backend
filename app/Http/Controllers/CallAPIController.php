<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;
use RolfHaug\TokenAuth\LoginToken;
use Illuminate\Support\Facades\Crypt;

use App\Models\VoucherBundle;
use App\Models\Ticket;
use App\Models\Stream;
use App\Models\Meeting;

class CallAPIController extends Controller
{
    function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'voucher_bundle_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            if (LoginToken::authenticate($data['token'])) {
                $voucher_bundle = VoucherBundle::where('id', $data['voucher_bundle_id'])->first();
                $voucher_bundle->ticket->event->theatre_json = null;
                $organization_owner_id = $voucher_bundle->ticket->event->organization->users()->first()->id;
                if (!$voucher_bundle || ($voucher_bundle->user_id !== auth()->user()->id && $organization_owner_id !== auth()->user()->id)) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Voucher ID is invalid'
                    ], 422);
                }
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => auth()->user(),
                        'voucher_bundle' => $voucher_bundle,
                        'host_id' => $organization_owner_id
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

    function liveStreamLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'ticket_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            if (LoginToken::authenticate($data['token'])) {
                $ticket = Ticket::where('id', $data['ticket_id'])->first();
                $ticket->event->theatre_json = null;
                $organization_owner_id = $ticket->event->organization->users()->first()->id;
                $voucher_bundle = VoucherBundle::where('user_id', auth()->user()->id)->where('ticket_id', $ticket->id)->get();
                if (!$ticket || (count($voucher_bundle) == 0 && $organization_owner_id !== auth()->user()->id)) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Ticket ID is invalid'
                    ], 422);
                }
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => auth()->user(),
                        'ticket' => $ticket,
                        'host_id' => $organization_owner_id
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

    function createNewStream(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stream_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        try {

            $stream = new Stream();
            $stream->user_id = auth()->user()->id;
            $stream->name = $request['stream_name'];
            $stream->is_private = $request['is_private'];
            $stream->save();
            return response()->json([
                'success' => 1,
                'message' => 'stream successfully created!',
                'data' => $stream
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    function createNewMeeting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meeting_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post(env('MEETING_BACKEND_URL') . '/query', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'operationName' => 'CreateChannel',
                    'variables' => [
                        'title' => $request['meeting_name'],
                        'backendURL' => env('MEETING_BACKEND_URL'),
                        'enablePSTN' => false
                    ],
                    'query' => 'mutation CreateChannel($title: String!, $backendURL: String!, $enablePSTN: Boolean) {
                                    createChannel(title: $title, backendURL: $backendURL, enablePSTN: $enablePSTN) {
                                        passphrase {
                                            host
                                            view
                                            __typename
                                        }
                                        channel
                                        title
                                        pstn {
                                            number
                                            dtmf
                                            __typename
                                        }
                                        __typename
                                    }
                                }',
                ])
            ]);
            $response = \json_decode($response->getBody()->getContents());
            $meeting = new Meeting();
            $meeting->user_id = auth()->user()->id;
            $meeting->name = $request['meeting_name'];
            $meeting->host_url = $response->data->createChannel->passphrase->host;
            $meeting->attendee_url = $response->data->createChannel->passphrase->view;
            $meeting->save();
            return response()->json([
                'success' => 1,
                'message' => 'meeting successfully created!',
                'data' => $meeting
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    function deleteMeeting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $meeting = Meeting::where('id', $request['id'])->first();
            if ($meeting->user->id !== auth()->user()->id) {
                return response()->json([
                    'success' => 0,
                    'message' => 'no permission to delete this meeting'
                ], 422);
            }
            $meeting->delete();
            return response()->json([
                'success' => 1,
                'message' => 'meeting successfully deleted!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    function MeetingLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'meeting_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            if (LoginToken::authenticate($data['token'])) {
                $meeting = Meeting::where('id', $data['meeting_id'])->first();

                if (!$meeting) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Meeting ID is invalid'
                    ], 422);
                }
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => auth()->user(),
                        'meeting' => $meeting,
                        'host_id' => $meeting->user->id
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

    function MeetingInviteLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invite_token' => 'required',
            'meeting_id' => 'required',
            'guest_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            $token = Crypt::decryptString($request['invite_token']);
            $token = substr($token, 14);
            if (LoginToken::authenticate($token)) {
                $meeting = Meeting::where('id', $data['meeting_id'])->first();

                if (!$meeting) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Meeting ID is invalid'
                    ], 422);
                }
                $guest = [
                    "name" => $request['guest_name'],
                    "avatar" => "https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png",
                    "id" => time()
                ];
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => $guest,
                        'meeting' => $meeting,
                        'host_id' => $meeting->user->id
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

    function StreamLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'stream_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            if (LoginToken::authenticate($data['token'])) {
                $stream = Stream::where('id', $data['stream_id'])->first();

                if (!$stream) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Stream ID is invalid'
                    ], 422);
                }
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => auth()->user(),
                        'stream' => $stream,
                        'host_id' => $stream->user->id
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

    function deleteStream(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $stream = Stream::where('id', $request['id'])->first();
            if ($stream->user->id !== auth()->user()->id) {
                return response()->json([
                    'success' => 0,
                    'message' => 'no permission to delete this stream'
                ], 422);
            }
            $stream->delete();
            return response()->json([
                'success' => 1,
                'message' => 'stream successfully deleted!'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    function StreamInviteLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invite_token' => 'required',
            'stream_id' => 'required',
            'guest_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            $token = Crypt::decryptString($request['invite_token']);
            $token = substr($token, 14);
            if (LoginToken::authenticate($token)) {
                $stream = Stream::where('id', $data['stream_id'])->first();

                if (!$stream) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Stream ID is invalid'
                    ], 422);
                }
                $guest = [
                    "name" => $request['guest_name'],
                    "avatar" => "https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png",
                    "id" => time()
                ];
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => $guest,
                        'stream' => $stream,
                        'host_id' => $stream->user->id
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

    function LiveInviteLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invite_token' => 'required',
            'ticket_id' => 'required',
            'guest_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            $token = Crypt::decryptString($request['invite_token']);
            $token = substr($token, 14);
            if (LoginToken::authenticate($token)) {
                $ticket = Ticket::where('id', $data['ticket_id'])->first();
                $ticket->event->theatre_json = null;
                $organization_owner_id = $ticket->event->organization->users()->first()->id;
                $voucher_bundle = VoucherBundle::where('user_id', auth()->user()->id)->where('ticket_id', $ticket->id)->get();
                if (!$ticket || (count($voucher_bundle) == 0 && $organization_owner_id !== auth()->user()->id)) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Ticket ID is invalid'
                    ], 422);
                }
                $guest = [
                    "name" => $request['guest_name'],
                    "avatar" => "https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png",
                    "id" => time()
                ];
                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => $guest,
                        'stream' => $ticket,
                        'host_id' => $organization_owner_id
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

    function CallInviteLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invite_token' => 'required',
            'ticket_id' => 'required',
            'guest_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $data = $request->json()->all();

        try {
            $token = Crypt::decryptString($request['invite_token']);
            $token = substr($token, 14);
            if (LoginToken::authenticate($token)) {
                $voucher_bundle = VoucherBundle::where('id', $data['ticket_id'])->first();
                $voucher_bundle->ticket->event->theatre_json = null;
                $organization_owner_id = $voucher_bundle->ticket->event->organization->users()->first()->id;
                if (!$voucher_bundle || ($voucher_bundle->user_id !== auth()->user()->id && $organization_owner_id !== auth()->user()->id)) {
                    return response()->json([
                        'success' => 0,
                        'message' => 'Voucher ID is invalid'
                    ], 424);
                }

                $guest = [
                    "name" => $request['guest_name'],
                    "avatar" => "https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png",
                    "id" => time()
                ];

                return response()->json([
                    'success' => 1,
                    'message' => 'success',
                    'data' => [
                        'user' => $guest,
                        'stream' => $voucher_bundle,
                        'host_id' => $organization_owner_id
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
        ], 423);
    }

    function generateOpenviduToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customSessionId' => 'required'
        ]);
        $openviduServerURL = "https://call.eventssmarter.com:8443/";
        $authorizationKey = 'Basic T1BFTlZJRFVBUFA6ZXZlbnRzMTIzc2VjcmV0';
        $client = new \GuzzleHttp\Client();
        try {
            $client->get($openviduServerURL . 'openvidu/api/sessions/' . $request['customSessionId'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationKey
                ]
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $client->post($openviduServerURL . 'openvidu/api/sessions', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $authorizationKey
                ],
                'body' => '{
                    "customSessionId":"' . $request['customSessionId'] . '"
                }'
            ]);
        }
        $response = $client->post($openviduServerURL . 'openvidu/api/sessions/' . $request['customSessionId'] . '/connection', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $authorizationKey
            ],
            'body' => '{
                "role":"SUBSCRIBER",
                "data":"{\"serverData\":{\"id\":1}}"
            }'
        ]);
        $response = \json_decode($response->getBody()->getContents());
        return response()->json([
            'success' => 1,
            'message' => 'success',
            'data' => [
                'token' => $response->token
            ]
        ]);
    }
}
