import './bootstrap';
import {createApp} from 'vue/dist/vue.esm-bundler.js';
import Emitter from 'pico-emitter';


import TicketPriceCalculator from './components/TicketPriceCalculator.vue';
import TicketPurchaseForm from './components/TicketPurchaseForm.vue';
import SiteSearchBar from './components/SiteSearchBar.vue';
import Testimonials from './components/Testimonials.vue';
import ContactInfo from './components/ContactInfo.vue';
import FilterList from './components/FilterList.vue';
import EditContent from './components/EditContent.vue';
import BankInformationForm from './components/BankInformationForm.vue';
import BookCallForm from './components/BookCallForm.vue';
import ReservationForm from './components/ReservationForm.vue';
import CartModal from './components/CartModal.vue';
import CartCheckout from './components/CartCheckout.vue';


const app = createApp({});

app.component('ticket-price-calculator', TicketPriceCalculator);
app.component('ticket-purchase-form', TicketPurchaseForm);
app.component('site-search-bar', SiteSearchBar);
app.component('testimonials', Testimonials);
app.component('contact-info', ContactInfo);
app.component('filter-list', FilterList);
app.component('edit-content', EditContent);
app.component('bank-information-form', BankInformationForm);
app.component('book-call-form', BookCallForm);
app.component('reservation-form', ReservationForm);
app.component('cart-modal', CartModal);
app.component('cart-checkout', CartCheckout);

const emitter = new Emitter();
app.config.globalProperties.emitter = emitter;

app.mount("#app");

$('div[data-follow]').on('click', function(e) {

    e.preventDefault();

    const organization_id = $(this).data('follow');

    if ($(this).find('i').hasClass('fa-heart-o')) {
        // change to filled heart
        $(`div[data-follow=${organization_id}]`).find('i').removeClass('fa-heart-o').addClass('fa-heart');
        $(`div[data-follow=${organization_id}]`).find('.text').text('Following');
    } else {
        // change to empty heart
        $(`div[data-follow=${organization_id}]`).find('i').removeClass('fa-heart').addClass('fa-heart-o');
        $(`div[data-follow=${organization_id}]`).find('.text').text('Follow');
    }

    axios.post('/follow', { organization_id })

});
