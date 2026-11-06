<template>
    <div class="line col-md-12">
        <div class="col-md-12">
            <label class="w-full">Purchase</label>
            <div v-for="bundle in bundles" :key="bundle.id" class="item col-md-3 col-sm-4" style="width:300px">
                <div class="thumbnail">
                    <h3 class="text-center" style="min-height:50px;">{{ bundle.bundle_name == 1 ? 'Normal Ticket' :
                        bundle.bundle_name }} x {{ bundle.count }}</h3>
                    <h4 class="text-center" style="min-height:50px;">Price : {{ formatPrice(bundle.price) }}</h4>
                    <div class="caption">
                        <div class="row more-detail category--v basket-count" style="">
                            <div class="col-xs-4" style="text-align:center">
                                <button type="button" data-action="minus" class="btn btn-secondary cart-minus btn-minus"
                                    @click="decrement(bundle)">-</button>
                            </div>
                            <div class="col-xs-4">
                                <div class="counter-box">
                                    <input :disabled="showNoTicketWarning" type="text" style="text-align:center"
                                        class="form-control" v-model="bundle.purchase_quantity">
                                    <input type="hidden" name="bundle_ids[]" :value="bundle.id">
                                    <input type="hidden" name="bundle_purchases[]" :value="bundle.purchase_quantity">
                                </div>
                            </div>
                            <div class="col-xs-4" style="text-align:center">
                                <button type="button" data-action="plus" class="btn btn-secondary cart-plus btn-plus"
                                    @click="increment(bundle)">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <p style="color:red; font-size:14pt" v-if="showLessTicketWarning">Only {{ leftQuantity }} more tickets
                available.
            </p>
            <p style="color:red; font-size:14pt" v-if="showNoTicketWarning">Tickets are sold out for this event.</p>
        </div>
        <div class="col-md-12  ticket-price">
            <label>Price : </label>
            <strong>{{ formatPrice(totalPrice) }}</strong>
        </div>
        <div class="col-md-12 ticket-price">
            <label>Quantity : </label>
            <strong>{{ quantity }}</strong>
            <input type="hidden" name="quantity" :value="quantity" id="purchase_quantity">
        </div>
        <div class="col-md-12 ticket-price">
            <label>Tax (6%) + Fee : </label>
            <strong>{{ formatPrice(fee) }}</strong>
        </div>
        <div class="col-md-6 col-sm-6 ticket-total-price">
            <label>Total Price : </label>
            <strong>{{ formatPrice(totalPrice + fee) }}</strong>
        </div>
    </div>
</template>
<script>
export default {
    props: ['oldBundles', 'leftQuantity', 'seat', 'currency', 'currencies'],
    data() {
        return {
            bundles: [],
            totalPrice: 0,
            quantity: 0,
            showLessTicketWarning: false,
            showNoTicketWarning: false,

            zero_decimal: false,
        }
    },
    mounted() {
        this.currencies.some(item => {
            if (item.code === this.currency) {
                this.zero_decimal = item.zero_decimal;
                return true;
            }
        });
        this.bundles = this.oldBundles.map((b, index) => {
            if (this.leftQuantity != 0 && this.oldBundles.length === 1) {
                b.purchase_quantity = index === 0 ? 1 : 0;
            }
            else {
                b.purchase_quantity = 0
            }
            if (this.seat) {
                b.purchase_quantity = 1
            }
            if (!this.zero_decimal) b.price = b.price / 100;
            return b;
        })
        if (this.leftQuantity == 0 && this.seat !== 1) {
            this.showNoTicketWarning = true
        }
        else if (parseInt(this.leftQuantity) < 6 && this.seat !== 1) {
            this.showLessTicketWarning = true
        }
    },
    computed: {
        fee() {
            return this.totalPrice > 0 ? this.totalPrice * 0.06 : 0
        }
    },
    methods: {
        increment(bundle) {
            if (!this.seat) {
                if (this.leftQuantity === 0) {
                    return
                }
                if (this.quantity >= this.leftQuantity) {
                    this.showLessTicketWarning = true
                    return
                }
                if ((this.quantity + bundle.count) > this.leftQuantity) {
                    this.showLessTicketWarning = true
                    return
                }
            }
            bundle.purchase_quantity++;
        },
        decrement(bundle) {
            if (bundle.purchase_quantity)
                bundle.purchase_quantity--;
        },
        formatPrice(price) {
            let formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: this.currency
            });
            return formatter.format(price);
        }
    },
    watch: {
        bundles: {
            handler: function (after, before) {
                this.totalPrice = 0;
                this.quantity = 0;
                this.bundles.map(b => {
                    this.totalPrice += (b.purchase_quantity * (b.price * 1000)) / 1000;
                    this.quantity += b.purchase_quantity * b.count;
                });
                if ((this.quantity > this.leftQuantity) && (this.leftQuantity != 0)) {
                    this.showLessTicketWarning = true
                }
                else if (this.leftQuantity > 5) {
                    this.showLessTicketWarning = false
                }
            },
            deep: true
        }
    }
}
</script>
