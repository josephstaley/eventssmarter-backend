<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VoucherBundle;
use Carbon\Carbon;
use App\Models\Ticket;
use App\Models\Stream;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class StreamAPIController extends Controller
{
    function streams()
    {
        $liveStreamTickets = Ticket::join('events', 'tickets.event_id', 'events.id')->select('tickets.*', 'events.ends_at')->whereNull('events.deleted_at')->where('is_live_stream', 1)->where('tickets.is_private', 0)->where('events.is_private', 0)->where('events.ends_at', '>=', Carbon::now())->with([
            'event' => function ($query) {
                $query->select('*')->with([
                    'organization' => function ($query_org) {
                        $query_org->select('id', 'category', 'name', 'slug');
                    }
                ]);
            }
        ])->orderBy('events.ends_at')->limit(5)->get()->makeHidden(['media_url', 'stream_url', 'attendee_url', 'rtmp_key', 'rtmp_uid', 'rtmp_url']);
        // $streams = Stream::where('is_private', 0)->where('ends_at', '>=', Carbon::now()->subMinutes(10)->toDateTimeString())->limit(5)->get();

        return $this->sendResponse($liveStreamTickets, 'Streams retrieved successfully');
    }

    function getMyStreams()
    {
        try {
            /** @var \App\Models\User */
            $user = auth()->user();
            $streams = $user->streams()->orderBy('updated_at', 'desc')->get();
            return $this->sendResponse($streams, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function createNewStream(Request $request)
    {
        $this->validate($request, [
            'stream_name' => 'required',
            'is_private' => 'required|numeric'
        ]);

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(env('AGORA_BACKEND_URL') . '/v1/login?project_id=' . env('AGORA_LIVESTREAM_PROJECT_ID') . '&platform_id=turnkey_web');
            $token = \json_decode($response->getBody()->getContents())->token;
            $response = $client->post(env('AGORA_BACKEND_URL') . '/v1/query', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'body' => json_encode([
                    'operationName' => 'CreateChannel',
                    'variables' => [
                        'title' => $request['stream_name'],
                        'enablePSTN' => false
                    ],
                    'query' => 'mutation CreateChannel($title: String!, $enablePSTN: Boolean) {
                                    createChannel(title: $title, enablePSTN: $enablePSTN) {
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
            $stream = new Stream();
            $stream->user_id = auth()->user()->id;
            $stream->name = $request['stream_name'];
            $stream->is_private = $request['is_private'];
            $stream->host_url = $response->data->createChannel->passphrase->host;
            $stream->attendee_url = $response->data->createChannel->passphrase->view;
            $stream->save();
            /** @var \App\Models\User */
            $user = auth()->user();
            $streams = $user->streams()->orderBy('updated_at', 'desc')->get();
            return $this->sendResponse($streams, 'stream successfully created!');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function deleteStream(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        try {
            $stream = Stream::where('id', $request['id'])->first();
            if (!$stream) {
                return $this->sendError('id is invalid', 422);
            }
            if ($stream->user->id !== auth()->user()->id) {
                return $this->sendError('No Permission', 422);
            }
            $stream->delete();
            /** @var \App\Models\User */
            $user = auth()->user();
            $streams = $user->streams()->orderBy('updated_at', 'desc')->get();
            return $this->sendResponse($streams, 'stream is successfully deleted!');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function getMyMeetings()
    {
        try {
            /** @var \App\Models\User */
            $user = auth()->user();
            $meetings = $user->meetings()->orderBy('updated_at', 'desc')->get();
            return $this->sendResponse($meetings, 'success');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function createNewMeeting(Request $request)
    {
        $this->validate($request, [
            'meeting_name' => 'required'
        ]);

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get(env('AGORA_BACKEND_URL') . '/v1/login?project_id=' . env('AGORA_MEETING_PROJECT_ID') . '&platform_id=turnkey_web');
            $token = \json_decode($response->getBody()->getContents())->token;
            $response = $client->post(env('AGORA_BACKEND_URL') . '/v1/query', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'body' => json_encode([
                    'operationName' => 'CreateChannel',
                    'variables' => [
                        'title' => $request['meeting_name'],
                        'enablePSTN' => false
                    ],
                    'query' => 'mutation CreateChannel($title: String!, $enablePSTN: Boolean) {
                                    createChannel(title: $title, enablePSTN: $enablePSTN) {
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
            /** @var \App\Models\User */
            $user = auth()->user();
            $meetings = $user->meetings()->orderBy('updated_at', 'desc')->get();
            return $this->sendResponse($meetings, 'meeting successfully created!');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function deleteMeeting(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        try {
            $meeting = Meeting::where('id', $request['id'])->first();
            if (!$meeting) {
                return $this->sendError('id is invalid', 422);
            }
            if ($meeting->user->id !== auth()->user()->id) {
                return $this->sendError('No Permission', 422);
            }
            $meeting->delete();
            /** @var \App\Models\User */
            $user = auth()->user();
            $meetings = $user->meetings()->orderBy('updated_at', 'desc')->get();
            return $this->sendResponse($meetings, 'meeting is successfully deleted!');
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), 422);
        }
    }

    function getStreamInformation(Ticket $ticket)
    {
        /** @var \App\Models\User */
        $user = auth()->user();
        $voucher = VoucherBundle::where('ticket_id', $ticket->id)->where('user_id', $user->id)->first();
        if ($voucher) {
            return $this->sendResponse($ticket, 'success');
        } else {
            if ($user->can('update', $ticket->event)) {
                return $this->sendResponse($ticket, 'success');
            }
            return $this->sendError('Permission Denied', 422);
        }
    }

    function summarizeText(Request $request)
    {
        if ($request->header('Origin') !== 'https://meet.eventssmarter.com') {
            return $this->sendError('CORS error', 403);
        }
        $fields = $this->validate($request, [
            'text' => 'required|string'
        ]);

        // Create the prompt for the AI API
        $prompt = "Summarize this transcript:\n"
            . "{$fields['text']}\n";
        $apiKey = env('MISTRAL_API_KEY');

        $client = new \GuzzleHttp\Client();

        try {
            // Send the POST request to the OpenAI API endpoint
            $response = $client->request('POST', 'https://api.mistral.ai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'open-mixtral-8x22b',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ],
            ]);

            // Decode the response
            $body = json_decode($response->getBody(), true);
            $description = trim($body['choices'][0]['message']['content'] ?? '');

            return $this->sendResponse($description, 'success');

        } catch (\Exception $th) {

            return $this->sendError($th->getMessage(), 500);
        }
    }

    function sendEmailSummarizedText(Request $request)
    {
        if ($request->header('Origin') !== 'https://meet.eventssmarter.com') {
            return $this->sendError('CORS error', 403);
        }
        $fields = $this->validate($request, [
            'text' => 'required|string',
            'email' => 'required|email'
        ]);

        try {
            Mail::to($fields['email'])->send(new \App\Mail\summarizedTranscript($fields['text']));

            return $this->sendResponse(null, 'success');

        } catch (\Exception $th) {

            return $this->sendError($th->getMessage(), 500);
        }
    }
}
