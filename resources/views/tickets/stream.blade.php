@extends('layouts.app')
@section('meta')
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
    @if (strpos($ticket->stream_url, 'live.eventssmarter.com/live') !== false)
        <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js" defer></script>
    @endif
    <style>
        .banner-item a img {
            width: auto;
            height: 160px;
            margin: auto;
        }

        .slick-slide {
            height: 160px;
        }

        @media only screen and (max-width: 767px) {
            .banner-container {
                width: 100vw !important;
            }
        }

        .paginate_button {
            margin-right: 10px;
            cursor: pointer;
        }

        body {
            margin-top: 125px !important;
        }

        .panel-body {
            min-height: 400px;
        }

        .presenter {
            position: relative;
            height: 500px !important;
        }

        .presenter button {
            display: none;
        }

        .presenter:hover button {
            position: absolute;
            right: 25px;
            bottom: 10px;
            z-index: 1;
            display: block;
        }

        .presenter video {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
        }

        .presenter .name {
            position: absolute;
            bottom: 15px;
            right: 25px;
            font-size: 15pt;
            background: black;
            color: white;
        }

        #welcome-message {
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        video {
            object-fit: contain;
        }

        .col-md-6.presenter {
            height: 400px !important;
        }

        .col-md-4.presenter {
            height: 350px !important;
        }

        video::-webkit-media-controls {
            display: none !important;
        }
    </style>
@endsection
@section('content')
    <div class="container">
        @if (sizeof($banner))
            <div class="row">
                <div class="col-md-12 banner-container">
                    <div class="event-banner">
                        @foreach ($banner as $key => $item)
                            <div class="banner-item">
                                <a href={{ $item->link_url }} target="_blank" onclick="banner_click({{ $item->id }})">
                                    <img data-u="image" src={{ $item->image }}>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="row" style="margin-top: 40px;">
            <div class="col-md-offset-1 col-md-10">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        {{ trans('event.live-stream-for') }} {{ $ticket->event->name }} @ {{ $organization->name }}
                    </div>

                    <div class="panel-body">
                        <!-- {!! $ticket->stream_url !!} -->
                        <?php
                        if (strpos($ticket->stream_url, 'youtu') !== false) {
                            $embed_code = preg_replace('/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', "<iframe width=\"100%\" height=\"500\" src=\"//www.youtube.com/embed/$2\" width='100%' height='500' allowfullscreen='allowfullscreen' webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen></iframe>", $ticket->stream_url);
                            echo $embed_code;
                        } elseif (strpos($ticket->stream_url, 'twitch') !== false) {
                            echo '<iframe src=' . $ticket->stream_url . " allowfullscreen='allowfullscreen' webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen width='100%' height='500'></iframe>";
                        } elseif (strpos($ticket->stream_url, 'live.eventssmarter.com/live') !== false) {
                            echo '<div id="live-layout"></div>
                                                        <h3 id="welcome-message">{{ trans("event.live-stream-will-start-soon") }}!</h3>';
                        } else {
                            echo '<iframe src=' . $ticket->stream_url . " allowfullscreen='allowfullscreen' webkitallowfullscreen mozallowfullscreen oallowfullscreen msallowfullscreen width='100%' height='500'></iframe>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            {{-- <div class="col-md-2">
            <div class="ad-container">
                <Adsense data-ad-client="ca-pub-1626164133919437" data-ad-slot="1626164133919437" data-ad-format="auto" :data-full-width-responsive="true">
                </Adsense>
            </div>
        </div> --}}
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js" defer></script>
    <script type="module">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function banner_click(id) {
        $.ajax({
            type: 'POST',
            url: '/banner_click',
            data: {
                id: id
            },
            success: function(data) {},
        });
    }
    $(document).ready(function(){

        $('.event-banner').slick({
            dots: false,
            infinite: true,
            speed: 500,
            slidesToShow: 5,
            autoplay: true,
            autoplaySpeed: 3000,
            arrows: false,
            pauseOnHover: true,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3,
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 230,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    });
</script>
    @if (strpos($ticket->stream_url, 'live.eventssmarter.com/live') !== false)
        <script type="module">
    AgoraRTC.disableLogUpload();
    AgoraRTC.setLogLevel(4);
    let remoteUsersCount = 0;
    var token = '<?php echo $agora_token ?>';
    var client = AgoraRTC.createClient({ mode: "live", codec: "vp8" });
    var localTracks = {
        videoTrack: null,
        audioTrack: null
    };
    var remoteUsers = {};
    // Agora client options
    var channelID = '<?php echo substr($ticket->stream_url, strpos($ticket->stream_url, "/live/") + 6) ?>';
    var options = {
        appid: '<?php echo env("AGORA_APP_ID") ?>',
        channel: 'live-' + channelID,
        uid: <?php echo auth()->user()->id ?>,
        token,
        role: "audience"
    };

    client.setClientRole(options.role);
    client.on("user-published", handleUserPublished);
    client.on("user-unpublished", handleUserUnpublished);
    client.join(options.appid, options.channel, options.token || null, options.uid);

    function handleUserPublished(user, mediaType) {
        const id = user.uid;
        remoteUsers[id] = user;
        subscribe(user, mediaType);
    }

    function handleUserUnpublished(user, mediaType) {
        const id = user.uid;
        if (mediaType === "video"){
            delete remoteUsers[id];
            $(`#player-wrapper-${id}`).remove();
            remoteUsersCount--;
            if (remoteUsersCount === 1){
                $(".presenter").attr('class', "presenter col-md-12");
            }
            else if (remoteUsersCount === 2){
                $(".presenter").attr('class', "presenter col-md-6");
            }
        }
        if (remoteUsersCount == 0){
            $('#welcome-message').css('display', 'block')
        }
    }

    async function subscribe(user, mediaType) {
        const uid = user.uid;
        await client.subscribe(user, mediaType);
        if (mediaType === 'video') {
            var streamElement = document.createElement('div');
            remoteUsersCount++;
            if (remoteUsersCount === 1){
                streamElement.className = "presenter col-md-12";
            }
            else if (remoteUsersCount === 2){
                streamElement.className = "presenter col-md-6";
                $(".presenter").attr('class', "presenter col-md-6");
            }
            else{
                streamElement.className = "presenter col-md-4";
                $(".presenter").attr('class', "presenter col-md-4");
            }
            streamElement.id = `player-wrapper-${uid}`;
            document.getElementById('live-layout').appendChild(streamElement);
            let div = document.createElement("div");
            div.innerHTML = "<button onclick='doFull(" + uid + ")'><i class='fa fa-expand'></i></button>"
            streamElement.appendChild(div);
            // var nameElement = document.createElement('span');
            // nameElement.innerHTML = clientData.name;
            // nameElement.className="name";
            // streamElement.appendChild(nameElement);
            user.videoTrack.play(`player-wrapper-${uid}`);
            $('#welcome-message').css('display', 'none')
        }
        if (mediaType === 'audio') {
            user.audioTrack.play();
        }
    }

    window.doFull = (uid) => {
        let video = $(`#player-wrapper-${uid} video`)[0];
        if (video.requestFullscreen) {
            video.requestFullscreen();
        } else if (video.mozRequestFullScreen) {
            video.mozRequestFullScreen();
        } else if (video.webkitRequestFullscreen) {
            video.webkitRequestFullscreen();
        } else if (video.msRequestFullscreen) {
            video.msRequestFullscreen();
        }
    }
</script>
    @endif
@endsection
