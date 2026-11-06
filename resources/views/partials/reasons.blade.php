<div class="welcome-reason-section">
    <div class="yellow-background"></div>
    <div class="container section">
        <div class="row">
            <div class="col-md-4">
                <div class="text-center reasons">
                    <div class="panel-body" style="padding: 10px">
                        <img src="/images/positive.png" id="reason-image-3">
                        <h3 style="margin-top: 20px">{{trans('general.landing.reasons.why_title_row1')}}</h3>
                        <hr>
                        <p class="text" style="height:67px">{{trans('general.landing.reasons.why_description', [ 'app_name' => env('APP_NAME') ])}}</p>
                        <br>
                        <div class="text-center">
                            <a href="/live-stream-production-instructions" class="btn btn-primary o">{{trans('general.landing.reasons.read_more')}}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center reasons">
                    <div class="panel-body"  style="padding: 10px">
                        <img src="/images/mon.png" id="reason-image-1">
                        <h3 style="margin-top: 20px">{{trans('general.landing.reasons.sell_title')}}</h3>
                        <hr>
                        <p class="text" style="height:67px">{{trans('general.landing.reasons.sell_description')}}</p>
                        <br>
                        <div class="text-center">
                            <a href="/register?sell_tickets=true" class="btn btn-primary o">{{trans('general.landing.reasons.sell_tickets')}}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center reasons last">
                    <div class="panel-body" style="padding: 10px">
                        <img src="/images/coin.png" id="reason-image-2">
                        <h3 style="margin-top: 20px">{{trans('general.landing.reasons.buy_title')}}</h3> 
                        <hr>
                        <p class="text" style="height:67px">{{trans('general.landing.reasons.buy_description', [ 'app_name' => env('APP_NAME') ])}}</p>
                        <br>
                        <div class="text-center">
                            <a href="/register?sell_tickets=true" class="btn btn-primary o">{{trans('general.landing.reasons.buy_tickets')}}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</div>