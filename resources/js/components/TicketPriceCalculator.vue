<template>
    <div class="row">
        <input type="hidden" name="price" :value="zero_decimal ? Math.ceil(price) : Math.ceil(price*100)">
        <input type="hidden" name="bundle_names[]" value="Normal Ticket">
        <input type="hidden" name="bundle_counts[]" :value="1">
        <input type="hidden" name="bundle_prices[]" :value="zero_decimal ? Math.ceil(price) : Math.ceil(price*100)">
        <div v-show="is_donation == 0">
            <div class="col-md-12">
                <label>{{ i18n.ticket_price }}</label>
            </div>
            <div class="col-md-12">
                <div class="input-group">
                    <span class="input-group-addon">{{ i18n.price_per_ticket }} <b>{{ currency_simbol }}</b> </span>
                    <input type="text" class="form-control" id="ticket_price" v-model="price">
                </div>
            </div>
            <div class="col-md-12">
                <br>
                <span>
                    <strong>{{ i18n.you_make }}:</strong> {{ youMakeDollars }} <br/>
                </span>
            </div>

            <div v-for="bundle in bundles" :key="bundle.index" style="margin-top: 10px;">
                <div class="col-sm-5 col-xs-12">
                    <div class="input-group">
                        <span class="input-group-addon">{{ i18n.bundle_name }}</span>
                        <input type="text" class="form-control" v-model="bundle.bundle_name">
                        <input type="hidden" name="bundle_names[]" :value="bundle.bundle_name">
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="input-group">
                        <span class="input-group-addon">{{ i18n.count }} </span>
                        <input type="text" class="form-control" v-model="bundle.count">
                        <input type="hidden" name="bundle_counts[]" :value="Number(bundle.count)">
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="input-group">
                        <span class="input-group-addon">{{ i18n.price }} <i class="fa fa-dollar" aria-hidden="true"></i></span>
                        <input type="text" class="form-control" v-model="bundle.price">
                        <input type="hidden" name="bundle_prices[]" :value="zero_decimal ? Math.ceil(bundle.price) : Math.ceil(bundle.price*100)">
                    </div>
                </div>
                <div class="col-sm-1 col-xs-12">
                    <div class="input-group" style="float:right;">
                        <button type="button" class="btn btn-primary" @click="removeBundle(bundle)"> x </button>
                    </div>
                </div>
            </div>

            <div class="col-md-12" style="margin-top: 10px;" v-if="!is_call">
                <button type="button" class="btn btn-primary" @click="addBundle">{{ i18n.add_bundle }}</button>
            </div>
            <input type="hidden" name="has_affiliate" :value="has_affiliate">
        </div>

        <div class="col-md-12" style="margin-top:40px">
            <div class="form-group">
                <label for="is_subscribe">{{ i18n.subscribe }}&nbsp;</label>
                <input type="checkbox" id="is_subscribe" v-model="is_subscribe">
                <input type="hidden" name="is_subscribe" :value="is_subscribe ? 1 : 0">
                <div v-if="is_subscribe" style="margin-left:20px;">
                    <div class="flex">
                        <label for="discount_percent" style="padding-top:8px">{{ i18n.discount_percent_for_subscribers }}&nbsp;</label>
                        <input class="form-control" id="discount_percent" type="number" :max="100" v-model="discount_percent" name="discount_percent" style="max-width:150px;margin-left:20px;text-align:right">
                        <span style="padding-top:8px;margin-left:5px">%</span>
                    </div>
                    <div style="font-size:9pt">
                        {{ i18n.discount_100percent_free }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 row" style="margin-top:10px">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="is_donation">{{ i18n.donation }}&nbsp;</label>
                    <input type="checkbox" id="is_donation" v-model="is_donation">
                    <input type="hidden" name="is_donation" :value="is_donation == true ? 1 : 0">
                </div>
            </div>
        </div>

        <div class="col-md-12 row" style="margin-top:10px">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="is_live_stream">{{ i18n.live_stream }}&nbsp;</label>
                    <input type="checkbox" id="is_live_stream" v-model="is_live_stream">
                    <input type="hidden" name="is_live_stream" :value="is_live_stream == true ? 1 : 0">
                    <div class="flex" v-if="is_live_stream">
                        <select v-if="is_live_stream" class="form-control" v-model="stream_type" name="stream_type" style="margin-left:20px;min-width:200px;">
                            <option value="0">{{ i18n.eventssmarter_stream }}</option>
                            <option value="1">{{ i18n.twitch_stream }}</option>
                            <option value="2">{{ i18n.other_stream }}</option>
                        </select>
                        <div style="margin-left:45px;margin-top:5px" class="flex">
                            <label for="is_private" style="min-width:55px">{{ i18n.private }}&nbsp;</label>
                            <input type="checkbox" id="is_private" v-model="is_private">
                        </div>
                        <a href="/live-stream-production-instructions" target="blank" style="margin-left:30px;margin-top:4px;"><i class="fa fa-question-circle-o"></i></a>
                    </div>
                    <input type="hidden" name="is_private" :value="is_private == true ? 1 : 0">
                </div>
            </div>
        </div>
        <div class="col-md-12 row" style="margin-left:5px;">
            <div class="col-md-6" v-if="is_live_stream && stream_type === '2' ">
                <div class="form-group">
                    <label for="stream_url">{{ i18n.livestream_url }}</label>
                    <input type="text" class="form-control" id="stream_url" v-model="stream_url" :placeholder="i18n.livestream_url">
                </div>
            </div>
            <div class="col-md-6" v-if="is_live_stream && stream_type === '1'">
                <div class="form-group">
                    <label for="twitch_channel">{{ i18n.twitch_channel_name }}</label>
                    <input type="text" class="form-control" id="twitch_channel" v-model="twitch_channel" :placeholder="i18n.twitch_channel_name">
                </div>
            </div>
        </div>
        <input type="hidden" name="stream_url" :value="stream_url">

        <div class="col-md-12 row" style="margin-top:10px">
            <div class="col-md-3">
                <label for="is_call">{{ i18n.call }}&nbsp;</label>
                <input type="checkbox" id="is_call" v-model="is_call">
                <input type="hidden" name="is_call" :value="is_call == true ? 1 : 0">
            </div>
        </div>
        <div class="col-md-11 row" v-if="is_call" style="margin-left:40px">
            <div class="col-md-7 col-sm-9 col-xs-12" style="display:flex;">
                <span style="min-width:140px;padding-top:8px">{{ i18n.call_available_time }}</span>
                <input type="time" class="form-control" v-model="call_available_start_time">
                <span style="min-width:50px;padding-top:8px;text-align:center">{{ i18n.to }}</span>
                <input type="time" class="form-control" v-model="call_available_end_time">
                <input type="hidden" name="call_available_start_time" :value="call_available_start_time">
                <input type="hidden" name="call_available_end_time" :value="call_available_end_time">
            </div>
            <div class="col-md-5 col-sm-5 form-group" style="display:flex;padding-top:8px;">
                <label for="call_available_weekends" style="font-weight:400;margin-right:20px">{{ i18n.available_weekends }}&nbsp;</label>
                <input type="checkbox" id="call_available_weekends" v-model="call_available_weekends">
                <input type="hidden" name="call_available_weekends" :value="call_available_weekends == true ? 1 : 0">
            </div>
            <div class="col-md-12 col-sm-5 flex">
                <label for="call_time" style="font-weight:400;margin-right:20px;padding-top:7px;">{{ i18n.call_duration_time }}&nbsp;</label>
                <select class="form-control" id="call_time" v-model="call_time" name="call_time" style="margin-left:5px;max-width:180px;">
                    <option value="00:05">5 {{ i18n.min }}</option>
                    <option value="00:15">15 {{ i18n.min }}</option>
                    <option value="00:30">30 {{ i18n.min }}</option>
                    <option value="00:45">45 {{ i18n.min }}</option>
                    <option value="01:00">1 {{ i18n.hour }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-12 row" style="margin-top:20px">
            <div class="col-md-3">
                <label for="is_reservation">{{ i18n.reservation }}&nbsp;</label>
                <input type="checkbox" id="is_reservation" v-model="is_reservation">
                <input type="hidden" name="is_reservation" :value="is_reservation == true ? 1 : 0">
            </div>
        </div>
        <div class="col-md-11 row" v-if="is_reservation" style="margin-left:40px">
            <div class="col-md-7 col-sm-9 col-xs-12" style="display:flex;">
                <span style="min-width:180px;padding-top:8px">{{ i18n.reservation_available_time }}</span>
                <input type="time" class="form-control" v-model="reservation_available_start_time">
                <span style="min-width:50px;padding-top:8px;text-align:center">{{ i18n.to }}</span>
                <input type="time" class="form-control" v-model="reservation_available_end_time">
                <input type="hidden" name="reservation_available_start_time" :value="reservation_available_start_time">
                <input type="hidden" name="reservation_available_end_time" :value="reservation_available_end_time">
            </div>
            <div class="col-md-5 col-sm-5 form-group" style="display:flex;padding-top:8px;">
                <label for="reservation_available_weekends" style="font-weight:400;margin-right:20px">{{ i18n.available_weekends }}&nbsp;</label>
                <input type="checkbox" id="reservation_available_weekends" v-model="reservation_available_weekends">
                <input type="hidden" name="reservation_available_weekends" :value="reservation_available_weekends == true ? 1 : 0">
            </div>
            <div class="col-md-12 col-sm-5 flex">
                <label for="reservation_time" style="font-weight:400;margin-right:10px;padding-top:7px;">{{ i18n.reservation_duration_time }}&nbsp;</label>
                <select class="form-control" id="reservation_time" v-model="reservation_time" name="reservation_time" style="margin-left:5px;max-width:180px;">
                    <option value="00:15">15 {{ i18n.min }}</option>
                    <option value="00:30">30 {{ i18n.min }}</option>
                    <option value="00:45">45 {{ i18n.min }}</option>
                    <option value="01:00">1 {{ i18n.hour }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-12 row" style="margin-top:20px" v-show="seat">
            <div class="col-md-3">
                <label for="is_call">{{ i18n.seat }}&nbsp;</label>
                <input type="checkbox" id="is_seat" v-model="is_seat">
                <input type="hidden" name="is_seat" :value="is_seat == true ? 1 : 0">
            </div>
        </div>
    </div>

</template>
<script>

    import moment from 'moment';
    export default {
        props: [
            'old',
            'appFeePercentage',
            'oldBundles',
            'hasAffiliate',
            'oldAffiliateEmail',
            'oldAffiliateAmount',
            'oldIsLiveStream',
            'oldIsPrivate',
            'oldStreamUrl',
            'oldIsDonation',
            'oldIsCall',
            'oldCallAvailableStartTime',
            'oldCallAvailableEndTime',
            'oldCallAvailableWeekends',
            "oldCallTime",
            'seat',
            'oldIsSeat',
            'oldIsReservation',
            'oldReservationAvailableStartTime',
            'oldReservationAvailableEndTime',
            'oldReservationAvailableWeekends',
            "oldReservationTime",
            "currency",
            "currencies",
            "old_is_subscribe",
            "old_discount_percent",
            "i18n"
            ],
        data() {
            return {
                price: null,
                youMake: null,
                ticketPrice: null,
                bundle_counts: [''],
                bundle_price: [''],
                bundles: [],
                affiliate_email: null,
                affiliate_amount: null,
                has_affiliate: 0,
                is_live_stream: 0,
                is_donation: this.oldIsDonation == 1? true : false,
                is_private: 0,
                stream_url: '',
                stream_type: "0",
                twitch_channel: null,
                // for videl call ticket
                is_call: 0,
                call_available_start_time: '00:00',
                call_available_end_time: '00:00',
                call_available_weekends: 0,
                call_time: '00:05',
                // seat
                is_seat: 0,
                // reservation
                is_reservation: 0,
                reservation_available_start_time: '00:00',
                reservation_available_end_time: '00:00',
                reservation_available_weekends: 0,
                reservation_time: '00:15',

                currency_simbol: null,
                zero_decimal: false,

                is_subscribe: 0,
                discount_percent: 0,
            }
        },
        mounted() {
            this.currencies.some(item => {
                if (item.code === this.currency){
                    this.currency_simbol = item.symbol;
                    this.zero_decimal = item.zero_decimal;
                    return true;
                }
            })
            if (this.old) this.price = this.old;
            this.has_affiliate = this.hasAffiliate;
            if (this.oldAffiliateEmail) this.affiliate_email = this.oldAffiliateEmail;
            if (this.oldAffiliateAmount) this.affiliate_amount = this.oldAffiliateAmount;
            if (this.oldIsLiveStream) this.is_live_stream = this.oldIsLiveStream == 1 ? true : false;
            if (this.oldIsPrivate) this.is_private = this.oldIsPrivate == 1 ? true : false;
            if (this.oldStreamUrl) this.stream_url = this.oldStreamUrl;
            if (this.oldIsCall) this.is_call = this.oldIsCall == 1 ? true : false;
            if (this.oldCallAvailableStartTime) this.call_available_start_time = this.oldCallAvailableStartTime;
            if (this.oldCallAvailableEndTime) this.call_available_end_time = this.oldCallAvailableEndTime;
            if (this.oldCallAvailableWeekends) this.call_available_weekends = this.oldCallAvailableWeekends == 1 ? true : false;
            if (this.oldIsReservation) this.is_reservation = this.oldIsReservation == 1 ? true : false;
            if (this.oldReservationAvailableStartTime) this.reservation_available_start_time = this.oldReservationAvailableStartTime;
            if (this.oldReservationAvailableEndTime) this.reservation_available_end_time = this.oldReservationAvailableEndTime;
            if (this.oldReservationAvailableWeekends) this.reservation_available_weekends = this.oldReservationAvailableWeekends == 1 ? true : false;
            if (this.oldIsSeat) this.is_seat = this.oldIsSeat == 1 ? true : false;

            if(this.oldBundles) {

                this.bundles = this.oldBundles.filter(bundle => {
                    return bundle.count !== 1;
                })
                if (!this.zero_decimal){
                    this.bundles.map(b => {
                        b.price = b.price / 100;
                    })
                }
            }

            if (this.stream_url.indexOf('twitch') !== -1){
                this.stream_type = "1"
                this.twitch_channel = this.stream_url.substr(this.stream_url.indexOf('?channel=') + 9, this.stream_url.indexOf('&', this.stream_url.indexOf('?channel=')) - this.stream_url.indexOf('?channel=') - 9)
            }
            else if (this.stream_url.indexOf('live.eventssmarter.com/live') !== -1){
                this.stream_type = "0"
            }
            else if (this.stream_url.trim().length > 0){
                this.stream_type = "2"
            }
            if (!this.is_seat){
                let seatMap = document.getElementById('seatBox');
                if (seatMap){
                    seatMap.style.display = 'none'
                }
            }
            else {
                document.getElementById('quantity-box').style.display = 'none'
            }
            if (this.oldCallTime){
                this.call_time = this.oldCallTime.substr(0, 5)
            }

            if (this.oldReservationTime){
                this.reservation_time = this.oldReservationTime.substr(0, 5)
            }

            if (this.old_is_subscribe){
                this.is_subscribe = this.old_is_subscribe ? true : false;
                this.discount_percent = this.old_discount_percent;
            }
        },
        computed: {
            youMakeDollars(){
                let amount = parseFloat(this.youMake);

                if(!amount) return "--";

                return amount.toLocaleString('en-US', {
                    style: 'currency',
                    currency: this.currency,
                });
            }
        },
        methods: {
            calcPrices() {
                let amount = this.price;
                amount = Math.round((((1.06 * amount) * (1 - 0.03) - 0.25) * (1 - this.appFeePercentage * 0.01) - 0.5 - amount * 0.06) * 100) / 100;
                this.youMake = this.price > 0 ? (amount > 0 ? amount : 0) : 0;
            },
            addBundle() {
                this.bundles.push({
                    count: '',
                    price: '',
                    index: new Date().getTime()
                })
            },
            removeBundle(bundle) {
                this.bundles = this.bundles.filter(function( obj ) {
                    return obj.count !== bundle.count;
                });
            },
            toggleAffiliate() {
                this.has_affiliate = this.has_affiliate == 1 ? 0 : 1;
                if(this.has_affiliate == 1) {
                    this.affiliate_email = '';
                    this.affiliate_amount = '';
                }
            }
        },
        watch: {
            price() {
                this.calcPrices();
            },
            twitch_channel (val) {
                if (val !== ''){
                    this.stream_url = 'https://player.twitch.tv/?channel=' + val + '&parent=eventssmarter.com&&parent=www.eventssmarter.com'
                }
            },
            is_live_stream(val){
                if (val){
                    this.is_call = false;
                    this.is_seat = false;
                    this.is_reservation = false;
                }
            },
            is_call(val){
                if (val){
                    this.is_live_stream = false;
                    this.is_seat = false;
                    this.is_reservation = false;
                }
            },
            is_reservation(val){
                if (val){
                    this.is_live_stream = false;
                    this.is_seat = false;
                    this.is_call = false;
                }
            },
            is_seat(val){
                if (val){
                    this.is_call = false
                    this.is_live_stream = false
                    this.is_donation = false
                    this.is_reservation = false
                    let seatMap = document.getElementById('seatBox');
                    if (seatMap){
                        seatMap.style.display = 'block'
                    }
                    document.getElementById('quantity-box').style.display = 'none'
                }
                else {
                    let seatMap = document.getElementById('seatBox');
                    if (seatMap){
                        seatMap.style.display = 'none'
                    }
                    document.getElementById('quantity-box').style.display = 'block'
                }
            },
            is_donation(val){
                if (val){
                    this.is_seat = false
                }
            },
            call_available_end_time(val, beforeval){
                if (!val || val == ''){
                    this.call_available_end_time = beforeval
                }
            },
            resource_available_end_time(val, beforeval){
                if (!val || val == ''){
                    this.reservation_available_end_time = beforeval
                }
            }
        }
    }
</script>

<style scoped>
    #is_live_stream {
        margin-top: 5px;
    }
</style>
