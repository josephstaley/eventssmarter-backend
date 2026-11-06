@extends('layouts.app')

@section('meta')
    <title>{{ trans('general.welcome.buy-sell-tickets-at') }} {{ env('APP_NAME') }}</title>
    <meta name="description" content="Events made simple. Find what's happening near you.">
    <meta name="keywords" content="events, sell tickets, buy tickets">
    <meta property="og:title" content="Buy Tickets with {{ env('APP_NAME') }}" />
    <meta property="og:image" content="{{ env('APP_URL') }}/images/carousel/1.jpg" />
    <meta property="og:description" content="Buy & Sell Tickets with {{ env('APP_NAME') }}" />
    <style>
        .container {
            visibility: hidden;
        }

        #video-frame {
            min-height: 600px;
        }

        .livestream {
            padding-top: 30px;
            background: #fff !important;
            overflow: hidden;
        }

        .livestream-start-btn {
            min-width: 380px;
            max-width: 500px;
            height: 50px;
            margin: auto;
            font-size: 14pt !important;
            padding-top: 12px !important;
            margin-bottom: 20px !important;
        }

        .livestream-start-btn:hover {
            -webkit-box-shadow: 0 3px 11px 3px rgb(0 0 0 / 13%);
            box-shadow: 0 3px 11px 3px rgb(0 0 0 / 13%);
        }

        @media (max-width : 400px) {
            .livestream-start-btn {
                width: 300px;
            }

            .livestream-start-btn span {
                font-size: 11pt;
            }
        }
    </style>
    <script type="text/javascript" src="js/modernizr.custom.53451.js" defer></script>
@endsection

@section('content')
    <div class="hero homepage">
        <div class="carousel fade-carousel slide w-full" data-ride="carousel" data-interval="4500">
            <div class="carousel-inner">
                <div class="item slides active">
                    <img src="images/carousel/blur_1.webp" data-src="/images/carousel/1.webp" class="d-block w-100 lazy" alt="">
                </div>
                <div class="item slides">
                    <img src="images/carousel/blur_2.webp" data-src="/images/carousel/2.jpg" class="d-block w-100 lazy" alt="">
                </div>
                <div class="item slides">
                    <img src="images/carousel/blur_3.webp" data-src="/images/carousel/3.jpg" class="d-block w-100 lazy" alt="">
                </div>
            </div>
        </div>
        <div class="bottom-baseline">
            <svg fill="#ffffff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1480 142.3" preserveAspectRatio="none">
                <path d="M704.6 52.7C430.3 1.4 174.9 23 0 43.7v98.7h1480V43.7c-192.5 22.8-441.9 71.3-775.4 9z"></path>
            </svg>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1 class="white-text">{{ trans('general.landing.hero.title') }}</h1>
                    <h2 class="subtitle">{{ trans('general.landing.hero.sub_title') }}</h2>
                </div>
                <div class="col-xs-12 col-md-8 col-md-offset-2">
                    <site-search-bar :cities="{{ \App\Models\Event::cities() }}" :categories="{{ \App\Models\Organization::categories() }}">
                        {{ csrf_field() }}
                    </site-search-bar>
                </div>
            </div>
        </div>
    </div>

    <div class="livestream upcoming_events_header_bottom">
        <h1 class="title" style="margin-bottom: 50px"><span style="color: #fb0">{{ trans('event.live-streams') }}</span></h1>
        <section id="dg-container" class="dg-container">
            <div class="dg-wrapper">
                @foreach ($streams as $item)
                    <a>
                        @if (is_a($item, \App\Models\Ticket::class))
                            <form class="dg-body" action="/checkout">
                                <input type="hidden" name="id" value="{{ $item->id }}">
                                @if (\Carbon\Carbon::now()->lte($item->event()->first()->ends_at))
                                    <span class="live">{{ trans('general.welcome.live') }}</span>
                                    <button type="submit" class="btn btn-primary watch">{{ trans('general.welcome.watch-now') }}</button>
                                @else
                                    <button type="button" class="btn btn-primary watch">{{ trans('general.welcome.streamed-on') }} &nbsp;{{ \Carbon\Carbon::parse($item->event()->first()->starts_at)->format('M d, Y') }}</button>
                                @endif
                                <img src="images/placeholder.jpg" class="lazy" data-src="/images/live-stream-photo.jpg">
                                <div class="information">
                                    <img src="images/placeholder.jpg" data-src="{{ $item->event()->first()->image }}" class="avatar lazy" alt="">
                                    <div class="event-information">
                                        <h4 class="name">{{ $item->event()->first()->name }}</h4>
                                        <button class="btn btn-primary w category">{{ $item->event->organization->category }}</button>
                                        <p class="description">{{ $item->event()->first()->description }}</p>
                                    </div>
                                </div>
                            </form>
                        @else
                            (is_a($item, \App\Models\Stream::class))
                            <form class="dg-body">
                                <span class="live">{{ trans('general.welcome.live') }}</span>
                                <button type="button" class="btn btn-primary watch" onclick="goStream({{ $item->id }})">{{ trans('general.welcome.watch-now') }}</button>
                                <img src="images/placeholder.jpg" class="lazy" data-src="/images/live-stream-photo.jpg">
                                <div class="information">
                                    <img src="images/placeholder.jpg" data-src="{{ $item->user->avatar }}" class="avatar lazy" alt="">
                                    <div class="event-information">
                                        <h4 class="name">{{ $item->name }}</h4>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </a>
                @endforeach
            </div>
            <nav>
                <button class="dg-prev btn">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <button class="dg-next btn">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </nav>
        </section>
    </div>

    <a href="/register?sell_tickets=true&livestream=true" class="btn btn-primary w livestream-start-btn">
        <i class="fa fa-video-camera" style="margin-right: 10px;"></i>
        <span> {{ trans('general.welcome.start-live-stream-now') }} </span>
    </a>

    <div class="upcoming_events_header">
        <div class="upcoming_events_header_bottom white-text">
            <h1 class="title">{{ trans('general.welcome.upcoming') }} <span style="color: #fb0">{{ trans('general.menu.events') }}</span></h1>
            <p class="title-border-calendar">
                <span class="hr"></span>
                <svg style="width: 23px;height: auto;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="hr"></span>
            </p>
            <div class="upcoming_buttons">
                <div class="upcoming_button_layout">
                    <a href="/events" class="btn btn-primary w">{{ trans('models.event.all') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/social" class="btn btn-primary w">{{ trans('models.event.social') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/esports" class="btn btn-primary w">{{ trans('models.event.esports') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/athletic" class="btn btn-primary w">{{ trans('models.event.athletic') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/political" class="btn btn-primary w">{{ trans('models.event.political') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/corporate" class="btn btn-primary w">{{ trans('models.event.corporate') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="upcoming-events">
        <div class="container">
            <div class="row">
                @foreach ($upcomming_events as $event)
                    @include('partials.event_card')
                @endforeach
            </div>
        </div>
    </div>
    @include('partials.reasons')
    <div class="container" style="margin-bottom: 30px">
        <div class="upcoming_events_header_bottom white-text">
            <h1 class="title">{{ trans('general.welcome.explore') }} <span style="color: #fb0">{{ trans('general.welcome.categories') }}</span></h1>
            <p class="title-border-calendar">
                <span class="hr"></span>
                <svg style="width: 23px;height: auto;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="hr"></span>
            </p>
            <div class="upcoming_buttons">
                <div class="upcoming_button_layout">
                    <a href="/organizations/social" class="btn btn-default yellow">{{ trans('models.event.social') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/esports" class="btn btn-default yellow">{{ trans('models.event.esports') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/athletic" class="btn btn-default yellow">{{ trans('models.event.athletic') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/political" class="btn btn-default yellow">{{ trans('models.event.political') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/corporate" class="btn btn-default yellow">{{ trans('models.event.corporate') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/dance" class="btn btn-default yellow">{{ trans('organization.dance') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/faith & worship" class="btn btn-default yellow">{{ trans('organization.faith-and-worship') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/thought leadership" class="btn btn-default yellow">{{ trans('organization.thought-leadership') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/education" class="btn btn-default yellow">{{ trans('organization.education') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/blockchain" class="btn btn-default yellow">{{ trans('organization.blockchain') }}</a>
                </div>
                <div class="upcoming_button_layout">
                    <a href="/organizations/philanthropy & humanitarian" class="btn btn-default yellow">{{ trans('organization.philanthropy-humanitarian') }}</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/social.webp);">
                    <a href="/organizations/social">
                        <div class="content">
                            <span class="primary">{{ trans('general.menu.social') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/esports.jpg);">
                    <a href="/organizations/esports">
                        <div class="content">
                            <span class="primary">{{ trans('organization.esports') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/sports.webp);">
                    <a href="/organizations/athletic">
                        <div class="content">
                            <span class="primary">{{ trans('organization.athletic') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/political.jpg);">
                    <a href="/organizations/political">
                        <div class="content">
                            <span class="primary">{{ trans('organization.political') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/corporate.jpg);">
                    <a href="/organizations/corporate">
                        <div class="content">
                            <span class="primary">{{ trans('organization.corporate') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/dance.webp);">
                    <a href="/organizations/dance">
                        <div class="content">
                            <span class="primary">{{ trans('organization.dance') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/faith.webp);">
                    <a href="/organizations/faith-and-worship">
                        <div class="content">
                            <span class="primary">{{ trans('organization.faith-and-worship') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/thought.webp);">
                    <a href="/organizations/thought-leadership">
                        <div class="content">
                            <span class="primary">{{ trans('organization.thought-leadership') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/education.webp);">
                    <a href="/organizations/education">
                        <div class="content">
                            <span class="primary">{{ trans('organization.education') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/blockchain.webp);">
                    <a href="/organizations/blockchain">
                        <div class="content">
                            <span class="primary">{{ trans('organization.blockchain') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="category-box" style="background-image: url(/images/category/philanthropyhumanitarian.webp);">
                    <a href="/organizations/philanthropy-and-humanitarian">
                        <div class="content">
                            <span class="primary" style="font-size: 2.1rem">{{ trans('organization.philanthropy-humanitarian') }}</span>
                            <span class="secondary">{{ trans('organization.events') }}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="js/jquery.gallery.js" defer></script>
    <script type="module">
    window.sr = ScrollReveal();
    sr.reveal('.container');

    var nav = document.querySelector("nav");
    var hero = document.querySelector(".hero");

    window.addEventListener("scroll", scrollBrain, false);
    scrollBrain()
    function scrollBrain() {
        if (window.pageYOffset >= hero.offsetTop + 64) {
            return nav.classList.add("scrolling")
        }
        if (nav.classList.contains("scrolling")) {
            return nav.classList.remove("scrolling")
        }
    }
    window.addEventListener("resize", resizeHandler);
    function resizeHandler(){
        var mapElement = document.getElementById("reason-image-1");
        mapElement.style.height = (mapElement.width + 40) + "px";
        var mapElement = document.getElementById("reason-image-2");
        mapElement.style.height = (mapElement.width + 40) + "px";
        var mapElement = document.getElementById("reason-image-3");
        mapElement.style.height = (mapElement.width + 40) + "px";
    }
    resizeHandler()
    $(function() {
        $('#dg-container').gallery();
    });
    window.goStream = (id) => {
        if ('{{ $login_token }}' !== ''){
            var url = 'https://call.eventssmarter.com/streams/' + id + '?{{ $login_token }}';
            window.location.href = url;
        }
        else {
            window.location.href = '/login';
        }
    }
</script>
@endsection
