<template>
    <div class="row">
        <div class="col-md-6 col-sm-6">
            <div class="row ticket-cart-item" v-for="item in items" :key="item.id">
                <div class="col-md-2 col-sm-2 col-xs-2">
                    <img :src="item.attributes.image" class="w-full" />
                </div>
                <div class="col-md-4 col-sm-3 col-xs-4" style="margin-top: 10px">
                    <a :href="item.attributes.event_link" target="blank" style="font-size: 13pt">{{ item.name }}</a>
                    <br />
                    {{ item.attributes.currency + " " + item.price * item.quantity }}
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6 ticket-cart-quantity">
                    <div class="quantity">
                        <button type="button" class="btn" @click="decreaseCartItem(item.id, item.quantity - 1)">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M48 31H16v2.462h32V31z" fill="currentColor"></path>
                            </svg>
                        </button>
                        <span>{{ item.quantity }}</span>
                        <button type="button" class="btn" @click="increaseCartItem(item.id, item.quantity + 1)">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M33.23 30.77H48v2.46H33.23V48h-2.46V33.23H16v-2.46h14.77V16h2.46v14.77z" fill="currentColor"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="col-md-offset-1 col-md-11" v-if="item.attributes.prompt">
                    <p>{{ item.attributes.prompt }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-6">
            <div v-for="(amount, currency) in total">
                <h4>{{ this.i18n.price }}: {{ currency }} {{ amount }}</h4>
                <h4 v-if="sub_total[currency] > 0">{{ this.i18n.promocode }}: {{ currency }} {{ '-' + sub_total[currency] }}</h4>
                <h4>{{ this.i18n.tax }}(6%): {{ currency }} {{ ((total[currency] - sub_total[currency]) * .06).toFixed(2) }}</h4>
                <h3 class="ticket-total-price">{{ this.i18n.total }}: {{ currency }} {{ ((total[currency] - sub_total[currency]) * 1.06).toFixed(2) }}</h3>
                <br>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["cartItems", 'i18n'],
    data() {
        return {
            items: [],
            total: 0,
            sub_total: 0,
        };
    },
    mounted() {
        this.sortAndStoreCartItems(this.cartItems);
        this.emitter.on("applyPromo", () => {
            if ($('#promo_code').val().trim() !== '') {
                swal({
                    text: this.i18n.apply_promocode,
                    buttons: false,
                    closeOnClickOutside: false,
                    icon: "info"
                });
                //$('#promo_code').prop('disabled', true);
                axios.post("/cart/checkout/promo-apply", {
                    promo_code: $('#promo_code').val()
                })
                    .then(({data}) => {
                        if (data.success) {
                            swal({
                                text: this.i18n.success,
                                buttons: false,
                                timer: 1000,
                                icon: "success"
                            });
                            localStorage.setItem('promo_code', $('#promo_code').val());
                            setTimeout(() => {
                                window.location.href = '?promo_code=' + $('#promo_code').val();
                            }, 2000)
                        }
                        else {
                            swal({
                                text: this.i18n.promocode_wrong,
                                showCancelButton: false,
                                icon: "error"
                            });
                        }
                    })
                    .catch(err => {
                        swal({
                            text: this.i18n.promocode_wrong,
                            showCancelButton: false,
                            icon: "error"
                        });
                    })
            }
        });
    },
    methods: {
        increaseCartItem(id, quantity) {
            axios.patch("/cart/update", { id, quantity }).then(({ data }) => {
                if (data.success) {
                    this.setNavQuantity(data.data.quantity);
                    this.sortAndStoreCartItems(data.data.items);
                }
            });
        },
        decreaseCartItem(id, quantity) {
            axios.patch("/cart/update", { id, quantity }).then(({ data }) => {
                if (data.success) {
                    this.setNavQuantity(data.data.quantity);
                    this.sortAndStoreCartItems(data.data.items);
                }
            });
        },
        setNavQuantity(quantity) {
            document.getElementById("cart-quantity").innerHTML = quantity;
        },
        sortAndStoreCartItems(_items) {
            _items.sort(function (a, b) {
                return a.attributes.timestamp - b.attributes.timestamp;
            });
            this.total = {};
            this.sub_total = {};
            _items.map((item) => {
                if (!this.sub_total[item.attributes.currency]) this.sub_total[item.attributes.currency] = 0;
                if (item.conditions.length > 0) {
                    this.sub_total[item.attributes.currency] += item.quantity * item.conditions[item.conditions.length - 1].parsedRawValue;
                }
                if (!this.total[item.attributes.currency]) this.total[item.attributes.currency] = 0;
                this.total[item.attributes.currency] += item.quantity * item.price;
            });
            this.items = _items;
        },
    },
};
</script>
<style scoped>
.ticket-cart-item {
    margin-bottom: 10px;
}

.ticket-cart-quantity {
    margin-top: 10px;
}

.ticket-cart-quantity .quantity {
    display: flex;
    align-items: center;
    height: 52px;
    transition: 0.2s ease-out;
    transition-property: color, border-color, background-color;
    padding-left: 8px;
    padding-right: 8px;
    justify-content: space-between;
    border: 1px solid #d6cbac9c;
    max-width: 160px;
}

.ticket-cart-quantity .price {
    font-size: 13.5pt;
    color: black;
}

.ticket-cart-quantity .quantity:hover {
    border-color: #fb0;
}

.ticket-cart-quantity .btn {
    padding: 2px;
    box-sizing: content-box;
    width: 20px;
    height: 20px;
    cursor: pointer;
    border-radius: 100%;
    transition: 0.2s ease-out;
    transition-property: color, border-color, background-color, box-shadow;
    background-color: #ffc2199e;
    color: black;
    border-color: transparent;
}

.ticket-cart-quantity .btn:focus,
.ticket-cart-quantity .btn:active {
    outline: #ffc2199e;
    border: #ffc2199e;
    padding: 3px;
    color: black;
    background-color: #ffc2199e;
}
</style>
