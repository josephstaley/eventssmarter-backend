<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('meta')

    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Styles -->
    <script src={{asset('js/yall.min.js')}} defer></script>
    <script type="module">
        document.addEventListener("DOMContentLoaded", yall);
    </script>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    @if(env('APP_ENV') !== 'local')
    <meta name="google-site-verification" content="ogIUdMRqPjA1RkBG2ckrGWAtncbfgWNlL9Ll_ta_pgY" />
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-K9PQ95G61P"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-K9PQ95G61P');
    </script>
    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
	  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
	  n.queue=[];t=b.createElement(e);t.async=!0;
	  t.src=v;s=b.getElementsByTagName(e)[0];
	  s.parentNode.insertBefore(t,s)}(window, document,'script',
	  'https://connect.facebook.net/en_US/fbevents.js');
	  fbq('init', '4363281683743314');
	  fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=4363281683743314&ev=PageView&noscript=1" /></noscript>
    <!-- End Facebook Pixel Code -->
    @endif
    @yield('style')

</head>

<body class="{{ request()->path() == '/' ? 'welcome' : '' }}">
    @if(env('APP_ENV') !== 'local')
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v5.0"></script>
    @endif
    <div id="app">
        @include('partials.nav')
        @include('partials.modals')
        @yield('content')
        @yield('nav-scripts')
    </div>

    @if(View::hasSection('page-title') && auth()->guest() && !isset($noShop))
    <div class="hero reasons-footer">
        @include('partials.reasons')
    </div>
    @endif
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <img src="/images/logo-white.png" alt="{{ env('APP_NAME') }} Logo" style="width: 240px">
                </div>


                <div class="col-md-12 text-center">
                    <div class="row useful-link" style="display:inline-flex;">
                        <div style="margin:10px 10px">
                            <a href="/terms">{{trans('general.footer.terms_of_use')}}</a>
                        </div>
                        <div style="margin:10px 10px">
                            <a href="/privacy">{{trans('general.footer.privacy_policy')}}</a>
                        </div>
                        <div style="margin:10px 10px">
                            <a href="/events">{{ trans('event.upcoming-events') }}</a>
                        </div>
                        <div style="margin:10px 10px">
                            <a href="/live-stream-production-instructions">{{ trans('general.menu.live-stream-guide') }}</a>
                        </div>
                        <div style="margin:10px 10px">
                            <a href="/contact">{{trans('general.footer.contact')}}</a>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    @include('partials.app_links')
                </div>
            </div>
        </div>
        <div class="termi">
            <div>
                Copyright <b>{{ date('Y') }}</b> EventsSmarter</a>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js" defer></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" defer></script>
    <script src="https://unpkg.com/scrollreveal/dist/scrollreveal.min.js" defer></script>
    @yield('scripts')
    @yield('scripts1')
    @yield('scripts2')
    @yield('scripts3')

    @if(env('APP_ENV') !== 'local')
    <!-- live chat -->
    <!-- Start of LiveChat (www.livechat.com) code -->
    <script>
        window.__lc = window.__lc || {};
    window.__lc.license = 14454858;
    ;(function(n,t,c){function i(n){return e._h?e._h.apply(null,n):e._q.push(n)}var e={_q:[],_h:null,_v:"2.0",on:function(){i(["on",c.call(arguments)])},once:function(){i(["once",c.call(arguments)])},off:function(){i(["off",c.call(arguments)])},get:function(){if(!e._h)throw new Error("[LiveChatWidget] You can't use getters before load.");return i(["get",c.call(arguments)])},call:function(){i(["call",c.call(arguments)])},init:function(){var n=t.createElement("script");n.async=!0,n.type="text/javascript",n.src="https://cdn.livechatinc.com/tracking.js",t.head.appendChild(n)}};!n.__lc.asyncInit&&e.init(),n.LiveChatWidget=n.LiveChatWidget||e}(window,document,[].slice))
    </script>
    <noscript><a href="https://www.livechat.com/chat-with/14454858/" rel="nofollow">Chat with us</a>, powered by <a href="https://www.livechat.com/?welcome" rel="noopener nofollow" target="_blank">LiveChat</a></noscript>
    <!-- End of LiveChat code -->
    @endif
    @desktop
    <script type="module">
        $(document).ready(function(){
            $(".dropdown").hover(function(){
                var dropdownMenu = $(this).children(".dropdown-menu");
                if(dropdownMenu.is(":visible")){
                    dropdownMenu.parent().toggleClass("open");
                }
            });
        });
    </script>
    @enddesktop
    <script src="https://js.pusher.com/8.0.1/pusher.min.js" defer></script>
    <script type="module">
        function calc_distance(lat1, lng1, lat2, lng2) {
            var R = 3958.8;
            var rlat1 = lat1*(Math.PI/180);
            var rlat2 = lat2*(Math.PI/180);
            var difflat = rlat2 - rlat1;
            var difflng = (lng2 - lng1) * (Math.PI/180);
            var d = 2 * R * Math.asin(Math.sqrt(Math.sin(difflat/2)*Math.sin(difflat/2)+Math.cos(rlat1)*Math.cos(rlat2)*Math.sin(difflng/2)*Math.sin(difflng/2)));
            return d;
        }

        var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_CLUSTER') }}",
        });
        var channel = pusher.subscribe("event-stored");

        navigator.geolocation.getCurrentPosition(function(position) {
            channel.bind('event-stored-event', function(data) {
                var diffInMile = calc_distance(data.lat, data.lng, position.coords.latitude, position.coords.longitude);
                if(diffInMile <= 50) {
                    if (Notification.permission === 'granted') {
                        var notification = new Notification('New Event of Eventssmarter', {
                            body: data.eventName + ' from ' + data.orgName
                        });
                    }
                    else if (Notification.permission !== 'denied') {
                        Notification.requestPermission().then(function(permission) {
                            if (permission === 'granted') {
                                var notification = new Notification('New Event of Eventssmarter', {
                                    body: data.eventName + ' from ' + data.orgName
                                });
                            }
                        });
                    }
                }
            });
        }, function(error) {
            // User has denied geolocation
        });
    </script>
</body>

</html>
