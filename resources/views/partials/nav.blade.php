<nav class="navbar navbar-fixed-top navbar-default {{ isset($homepage) ? 'homepage' : '' }}">
    <div class="container top" id="navbar-container">
        <a style="float:left;position:absolute;z-index:-1;height:45px" href="/">
            <img class="logo" src="/images/logo-white.png" alt="{{ env('APP_NAME') }}" <?php if (session()->has('locale') && session()->get('locale') != 'en') echo 'style="height:40px"' ?>>
        </a>
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <button class="btn btn-lang dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    {{ session()->get('locale') ? session()->get('locale') : 'EN' }} <span class="caret"></span>
                </a>
                <ul class="dropdown-menu lang center" role="menu">
                    <li>
                        <a class="dropdown-item" href="{{ url('lang/change?lang=en') }}">English</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ url('lang/change?lang=es') }}">Español</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ url('lang/change?lang=bg') }}">Български</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ url('lang/change?lang=ro') }}">Românesc</a>
                    </li>
                </ul>
            </button>
            <a class="navbar-brand" href="/">
                <img class="logo" src="/images/logo.png" alt="{{ env('APP_NAME') }}" <?php if (session()->has('locale') && session()->get('locale') != 'en') echo 'style="height:40px"' ?>>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                @if (Route::has('login'))
                    @auth
                        <li><a href="https://nft.eventssmarter.com"><span style="padding-left: 3px;">NFT</span> <br /><span class="nft-new">new</span></a></li>
                        <li><a href="{{ url('/organizations/social') }}">{{ trans('general.menu.social') }}</a></li>
                        <li><a href="{{ url('/organizations/political') }}">{{ trans('general.menu.political') }}</a></li>
                        <li><a href="{{ url('/organizations/athletic') }}">{{ trans('general.menu.athletic') }}</a></li>
                        <li><a href="{{ url('/organizations/corporate') }}">{{ trans('general.menu.corporate') }}</a></li>
                        <li><a href="{{ url('/organizations/esports') }}">{{ trans('models.event.esports') }}</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ trans('general.menu.more') }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu center" role="menu">
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/education') }}">{{ trans('organization.education') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/faith-and-worship') }}">{{ trans('organization.faith-and-worship') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/blockchain') }}">{{ trans('organization.blockchain') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/thought-leadership') }}">{{ trans('organization.thought-leadership') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/dance') }}">{{ trans('organization.dance') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/philanthropy-and-humanitarian') }}">{{ trans('organization.philanthropy-humanitarian') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('live-stream-production-instructions') }}">{{ trans('general.menu.live-stream-guide') }}</a>
                                </li>
                            </ul>
                        </li>

                        <li><a href="{{ url('/events') }}">{{ trans('general.menu.events') }}</a></li>
                        <li><a href="{{ url('/tickets') }}">{{ trans('general.menu.tickets') }}</a></li>
                        <li class="dropdown">
                            @handheld
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                            @elsehandheld
                                <a href="{{ url('/home') }}" class="dropdown-toggle" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                            @endhandheld

                            <ul class="dropdown-menu" role="menu">
                                @if (auth()->user()->isAdmin())
                                    <li>
                                        <a class="dropdown-item" href="{{ url('admin') }}">{{ trans('general.role.admin') }}</a>
                                    </li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="{{ url('/home') }}">{{ trans('general.menu.dashboard') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit(); localStorage.removeItem('referral_code');">
                                        {{ trans('auth.logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li><a href="https://nft.eventssmarter.com"><span style="padding-left: 3px;">NFT</span> <br /><span class="nft-new">new</span></a></li>
                        <li><a href="{{ url('/organizations/social') }}">{{ trans('general.menu.social') }}</a></li>
                        <li><a href="{{ url('/organizations/political') }}">{{ trans('general.menu.political') }}</a></li>
                        <li><a href="{{ url('/organizations/athletic') }}">{{ trans('general.menu.athletic') }}</a></li>
                        <li><a href="{{ url('/organizations/corporate') }}">{{ trans('general.menu.corporate') }}</a></li>
                        <li><a href="{{ url('/organizations/esports') }}">{{ trans('models.event.esports') }}</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ trans('general.menu.more') }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu center" role="menu">
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/education') }}">{{ trans('organization.education') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/faith-and-worship') }}">{{ trans('organization.faith-and-worship') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/blockchain') }}">{{ trans('organization.blockchain') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/thought-leadership') }}">{{ trans('organization.thought-leadership') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/dance') }}">{{ trans('organization.dance') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('organizations/philanthropy-and-humanitarian') }}">{{ trans('organization.philanthropy-humanitarian') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('live-stream-production-instructions') }}">{{ trans('general.menu.live-stream-guide') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li><a {!! \App\Models\Helpers::href('events') !!}>{{ trans('general.menu.events') }}</a></li>
                        <li><a {!! \App\Models\Helpers::href('login') !!}>{{ trans('auth.login') }}</a></li>
                        <li><a {!! \App\Models\Helpers::href('decide', 'btn btn-primary signup') !!}>{{ trans('auth.signup') }}</a></li>
                    @endauth
                @endif
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        {{ session()->get('locale') ? session()->get('locale') : 'EN' }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu lang center" role="menu">
                        <li>
                            <a class="dropdown-item" href="{{ url('lang/change?lang=en') }}">English</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('lang/change?lang=es') }}">Español</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('lang/change?lang=bg') }}">Български</a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ url('lang/change?lang=ro') }}">Românesc</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" data-toggle="modal" data-target="#cartModal">
                        <svg style="width: 22px" class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span style="position: absolute; top: 6px;" id="cart-quantity">{{ Cart::getTotalQuantity() }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    @if (View::hasSection('page-title'))
        <div class="container-fluid search-nav">
            <div class="container">
                <site-search-bar :cities="{{ \App\Models\Event::cities() }}" :categories="{{ \App\Models\Organization::categories() }}">
                    {{ csrf_field() }}
                </site-search-bar>
            </div>
        </div>
    @endif
</nav>
@section('nav-scripts')
    <cart-modal
        :cart-items="{{ json_encode(array_values(\Cart::getContent()->toArray()), true) }}"
        :i18n="{{ json_encode([
            'cart_summary' => trans('general.cart.cart-summary'),
            'quantity' => trans('general.cart.quantity'),
            'no_tickets_cart' => trans('general.cart.no-tickets-cart'),
            'taxes_calculate_at_checkout' => trans('general.cart.taxes-calculate-at-checkout'),
            'total' => trans('general.cart.total'),
            'checkout' => trans('general.cart.checkout'),
        ]) }}"
        >
    </cart-modal>
@endsection
