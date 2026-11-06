<template>
    <div class="modal fade right" id="cartModal">
        <div class="modal-dialog cartModal" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ i18n.cart_summary }}</h4>
                </div>
                <div class="modal-body">
                    <div id="cart-modal-content">
                        <template v-if="items.length > 0">
                            <div class="ticket-cart-item" v-for="item in items" :key="item.id">
                                <div class="ticket-cart-information">
                                    <img :src="item.attributes.image" :alt="item.name" />
                                    <h4 class="name">{{ item.name }}</h4>
                                    <a
                                        class="delete"
                                        @click="
                                            deleteCartItem(item.id);
                                        "
                                    >
                                        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M22 4v6.47H12v3.236h40V10.47H42V4H22zm3.333 6.47V7.235H38.67v3.235H25.333zm20.001 9.707h3.333V59H15.334V20.177h3.333v35.588h26.667V20.177zm-15 29.116V23.412h3.334v25.881h-3.334z" fill="currentColor"></path>
                                        </svg>
                                    </a>
                                </div>
                                <div class="ticket-cart-quantity">
                                    <p>{{ i18n.quantity }}</p>
                                    <div class="ticket-cart-information">
                                        <div class="quantity">
                                            <button class="btn" @click="decreaseCartItem(item.id, item.quantity - 1)">
                                                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M48 31H16v2.462h32V31z" fill="currentColor"></path>
                                                </svg>
                                            </button>
                                            <span>{{ item.quantity }}</span>
                                            <button class="btn" @click="increaseCartItem(item.id, item.quantity + 1)">
                                                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M33.23 30.77H48v2.46H33.23V48h-2.46V33.23H16v-2.46h14.77V16h2.46v14.77z" fill="currentColor"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="price">
                                            {{ item.attributes.currency + " " + item.price * item.quantity }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <h4 class="text-center" style="padding:20px">
                                {{ i18n.no_tickets_cart }}!
                            </h4>
                        </template>
                    </div>
                </div>
                <div class="modal-footer">
                    <div>
                        <div class="total" v-if="Object.keys(total).length > 0">
                            {{ i18n.taxes_calculate_at_checkout }}.
                            <h4>
                                <span>
                                    {{ i18n.total }}
                                </span>
                                <span class="amount">
                                    <span v-for="(item, index) in total" :key="index">
                                        {{ index + ' ' + item }} <br/>
                                    </span>
                                </span>
                            </h4>
                        </div>
                        <div class="flex">
                            <button type="button" class="btn btn-primary btn-checkout" :disabled="items.length == 0" @click="checkout">
                                <div class="flex">
                                    <div>{{ i18n.checkout }}</div>
                                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M51.364 30.158H6v3.423h45.628l-9.148 9.055L44.868 45 58 32 44.868 19l-2.388 2.364 8.884 8.794z" fill="currentColor"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["cartItems", "i18n"],
    data() {
        return {
            items: [],
            total: {},
        };
    },
    mounted() {
        this.sortAndStoreCartItems(this.cartItems);
        this.emitter.on("addToCart", (ticket_id) => {
            axios.post("/cart", { ticket_id }).then(({ data }) => {
                if (data.success) {
                    this.sortAndStoreCartItems(data.data.items);
                    this.setNavQuantity(data.data.quantity);
                    $("#cartModal").modal('show');
                }
            });
        });
    },
    methods: {
        deleteCartItem(id) {
            axios.put("/cart", { id }).then(({ data }) => {
                if (data.success) {
                    this.setNavQuantity(data.data.quantity);
                    this.sortAndStoreCartItems(data.data.items);
                    if (data.data.quantity === 0) {
                        $("#cartModal").modal("hide");
                    }
                }
            });
        },
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
        checkout() {
            window.location.href = '/cart/checkout';
        },
        sortAndStoreCartItems(_items) {
            _items.sort(function(a, b) {
                return a.attributes.timestamp - b.attributes.timestamp;
            });
            this.total = {};
            _items.map(item => {
                if (this.total[item.attributes.currency]){
                    this.total[item.attributes.currency] += item.quantity * item.price
                }
                else {
                    this.total[item.attributes.currency] = item.quantity * item.price
                }
            })
            this.items = _items;
        },
    },
};
</script>
<style scoped>
.ticket-cart-information {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ticket-cart-information img {
    height: 50px;
}

.ticket-cart-information .name {
    flex: 1;
    padding-left: 8px;
}

.ticket-cart-information a.delete {
    cursor: pointer;
}

.ticket-cart-information a.delete svg {
    height: 30px;
    width: 30px;
}

.ticket-cart-quantity {
    margin-top: 10px;
}

.ticket-cart-quantity p {
    margin-bottom: 3px;
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
    width: 50%;
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

.btn-checkout {
    flex: 1;
    height: 45px;
}

.btn-checkout .flex {
    align-items: center;
    justify-content: center;
}

.btn-checkout .flex div {
    flex: 1;
}

.btn-checkout svg {
    height: 20px;
}
#cart-modal-content .ticket-cart-item:last-child {
    border-bottom: none;
}

.ticket-cart-item {
    padding: 20px;
    border-bottom: 1px solid #e5e5e5;
}
#cartModal .modal-body {
    padding: 0px;
    overflow: hidden;
}
#cartModal .modal-body #cart-modal-content {
    height: 100%;
    overflow-x: hidden;
}
a.delete.disabled {
    pointer-events: none;
}
.modal-footer .total{
    text-align: left;
}
.modal-footer .total h4{
    color: black;
    display: grid;
    grid-template-columns: 50% 50%;
    padding: 3.2px 0;
    position: relative;
}
.modal-footer .total h4 .amount{
    text-align: right;
}
</style>
