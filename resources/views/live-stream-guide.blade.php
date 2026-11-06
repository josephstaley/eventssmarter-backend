@extends('layouts.app')

@section('meta')
    <title>LiveStream Production Instructions | {{ env('APP_NAME') }}</title>
    <style>
        .terms {
            color: black;
        }

        .bold {
            font-weight: bold;
        }

        .terms p {
            font-family: Arial;
            font-size: 12pt;
        }

        .terms h2 {
            margin-bottom: 10px;
        }

        .terms h3 {
            line-height: 115%;
            font-family: Arial;
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .terms h4 {
            font-family: Arial;
            font-size: 15px;
            font-weight: bold;
        }

        .terms li {
            font-size: 14px;
        }

        .underline {
            text-decoration: underline;
        }
    </style>
@endsection

@section('content')
    <div class="container terms">

        <div class="row">

            <div class="col-xs-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h2 class="text-center"> <i class="fa fa-video-camera"></i> Create a Live Stream on EventsSmarter Site</h2>
                        <br>
                        <p><b>1.</b> Login in www.EventsSmarter.com as an Organizer.</p>
                        <p><b>2.</b> Create an organization by clicking
                            <b>
                                <a class="underline" href="{{ route('organization.create') }}">
                                    <i class="fa fa-plus"></i> {{ trans('models.organization.add') }}
                                </a>
                            </b>
                            in <b><a class="underline" href="https://www.EventsSmarter.com/home">http://www.EventsSmarter.com/home</a></b> page or on your Dashboard.
                        </p>
                        <p><b>3.</b> After creating an organization, you now can create new events.</p>
                        <ul>
                            <li> Choose event logo (image files).</li>
                            <li> Event start and end time (this will be the live stream start time and
                                end time as well).
                            </li>
                            <li> You may attach a pdf file such as an event flyer.</li>
                        </ul>
                        <p><b>4.</b> The event page allows you to create your tickets.</p>
                        <ul>
                            <li> Choose your quantity and pricing</li>
                            <li> Live Streaming | We Provide 3 Options</li>
                            <ol>Events Smarter Live Stream software (*Recommended)</ol>
                            <ol>Twitch Stream (twitch channel name)</ol>
                            <ol>Other Streams (insert streaming link)</ol>
                        </ul>
                        <ul>
                            <li> Always Test to Make sure Stream is working</li>
                            <li> If you use another platform make sure it allows 3rd partying embedding and keep private so you can monetize without your link being shared.</li>
                        </ul>
                        <p><b>5.</b> Once live stream ticket is created, customers can purchase your ticket.</p>
                        <p><b>6.</b> Go to the live stream link.</p>
                        <ul>
                            <li>On the ticket section of event page, you can see live streams.</li>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/1.png">
                            <li>After click it, you can view the live stream display.</li>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/2.png">
                            <li>This link allows you can to the live stream of Events Smarter (live.eventssmarter.com)</li>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/3.png">
                        </ul>
                        <p><b>7.</b> On live stream site, as organizer/camera production, you can choose cameras and audio from list and join in stream.</p>
                        <ul>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/4.png">
                        </ul>
                        <ul>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/5.png">
                        </ul>
                        <ul>
                            <li>You can disable or enable audio or camera by footer buttons.</li>
                        </ul>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h2 class="text-center"> <i class="fa fa-video-camera"></i> Create a Live Stream on OBS Studio Tool</h2>
                        <br>
                        <p><b>1.</b> Download <a class="underline" href="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/OBS-Studio-27.0.1-Full-Installer-x64.exe">OBS studio tool <i class="fa fa-external-link"></i></a> and install it.</p>
                        <p><b>2. </b> Download <a class="underline" href="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/Agora-Tool-3.3.0-Installer.exe">Agora Tool <i class="fa fa-external-link"></i></a> and install it – this should be installed only after the above OBS studio tool.</p>
                        <p><b>3. </b> Download the configuration file locate in the ticket section of the event page.</p>
                        <ul>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/6.png">
                        </ul>
                        <p><b>4. </b> Open OBS Studio and click Tool/Agora RCT Tool.</p>
                        <ul>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/7.png">
                        </ul>
                        <p><b>5. </b> Upload downloaded config file in settings page and click start streaming to Agora. You can insert any device or media sources in this tool and can control them.</p>
                        <ul>
                            <img class="w-full" src="https://eventssmarter.s3.us-east-2.amazonaws.com/programs/8.png">
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
