<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\CallAPIController;
use App\Http\Controllers\OrganizationAdController;
use App\Http\Controllers\EventAdController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LangController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');
Route::post('/', [HomeController::class, 'search']);
// Route::get('/about', [HomeController::class, 'about']);
Route::get('/free-events', [HomeController::class, 'free_events']);
Route::get('/why', [HomeController::class, 'why']);
Route::get('/privacy', [HomeController::class, 'privacy']);
Route::get('/privacy-eu', [HomeController::class, 'privacy_eu']);
Route::get('/terms', [HomeController::class, 'terms']);
Route::get('/contact', [HomeController::class, 'contact']);
Route::get('/live-stream-production-instructions', [HomeController::class, 'livestream_guide']);
Route::get('/Live-Stream-Production-Instructions', [HomeController::class, 'livestream_guide']);
Route::get('/best-alternative-leading-competitor', [HomeController::class, 'bestAlternativeLeadingCompetitor']);

Route::post('banner_click', [HomeController::class, 'banner_click']);
Route::get('/recover/org-password', [HomeController::class, 'recoverOrgPassword']);

//affiliate stripe register (callback from connect.stripe.0auth)
Route::get('stripe/affiliate', [TicketController::class, 'affiliate_register'])->name('affiliate.register');
Route::get('stripe/promo', [OrganizationController::class, 'affiliate_register'])->name('promo.register');

Auth::routes();

Route::get('admin', [AdminController::class, 'index']);
Route::get('admin/banner', [AdminController::class, 'banner_show'])->name('Admin.banner_show');
Route::get('admin/banner/view/{id}', [AdminController::class, 'banner_view'])->name('Admin.banner_view');
Route::get('admin/banner/{id}/delete', [AdminController::class, 'banner_destory'])->name('Admin.banner_destroy');

Route::get('admin/create_banner', [AdminController::class, 'banner_create']);
Route::post('admin/store_banner', [AdminController::class, 'banner_store'])->name('Admin.banner_store');

Route::get('admin/getUsers', [AdminController::class, 'getUsers'])->name('Admin.getUsers');
Route::get('admin/users', [AdminController::class, 'userIndex']);
Route::get('admin/users/{user}', [AdminController::class, 'getUser'])->name('Admin.getUser');
Route::put('admin/users/{user}', [AdminController::class, 'updateUser'])->name('Admin.updateUser');
Route::patch('admin/users/{user}', [AdminController::class, 'signUser'])->name('Admin.signUser');
Route::delete('admin/users/{user}', [AdminController::class, 'deleteUser'])->name('Admin.deleteUser');
Route::post('admin/users/sendTicketLinkToken', [AdminController::class, 'sendTicketLinkToken'])->name('Admin.sendTicketLinkToken');

Route::get('admin/getEvents', [AdminController::class, 'getEvents'])->name('Admin.getEvents');
Route::get('admin/events', [AdminController::class, 'eventIndex']);
Route::get('admin/events/{id}/export', [AdminController::class, 'eventExport'])->name('event.adminExportForStripe');
Route::delete('admin/events/{event}', [AdminController::class, 'deleteEvent'])->name('Admin.deleteEvent');

Route::get('admin/{model}', [AdminController::class, 'show']);
Route::post('admin/content/{content}', [AdminController::class, 'update']);
Route::get('admin/{model}/edit/{id}', [AdminController::class, 'edit']);
Route::post('admin/{model}/{id}', [AdminController::class, 'update']);
Route::get('admin/{model}/{id}/delete', [AdminController::class, 'destroy']);

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::get('home/getCustomers', [HomeController::class, 'getCustomers'])->name('home.getCustomers');
    Route::post('home/notification', [HomeController::class, 'notification'])->name('home.notification');

    Route::get('/home/edit/{id}', [ProfileController::class, 'edit'])->name('edit.profile');
    Route::post('/home/update/{id}', [ProfileController::class, 'update'])->name('update.profile');
    Route::post('/change/password/{id}', [ProfileController::class, 'updatePassword'])->name('change.password');
    Route::post('/deactivate/{id}', [ProfileController::class, 'deactivate'])->name('deactivate.profile');

    Route::get('{organization}/{title}_{event}/banners', [BannerController::class, 'create'])->name('banners.create');
    Route::post('{organization}/{title}_{event}/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::get('{organization}/{id}/banners/edit', [BannerController::class, 'edit'])->name('banners.edit');
    Route::post('{organization}/{id}/banners/update', [BannerController::class, 'update'])->name('banners.update');
    Route::get('banners/destroy/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

    Route::get('{organization}/{title}_{event}/tickets', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('{organization}/{id}/tickets/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::get('{organization}/{title}_{event}/{ticket}', [EventController::class, 'show'])->name('tickets.show');
    Route::post('{organization}/{title}_{event}/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('{organization}/{id}/tickets/update', [TicketController::class, 'update'])->name('tickets.update');

    Route::get('organization', [OrganizationController::class, 'create'])->name('organization.create');
    Route::post('organization', [OrganizationController::class, 'store'])->name('organization.store');
    Route::get('{organization}/edit', [OrganizationController::class, 'edit'])->name('organization.edit');
    Route::post('{organization}/change', [OrganizationController::class, 'change'])->name('organization.change');
    Route::patch('{organization}', [OrganizationController::class, 'update'])->name('organization.update');
    Route::delete('{organization}', [OrganizationController::class, 'destroy'])->name('organization.destroy');

    Route::get('{organization}/events', [EventController::class, 'create'])->name('events.create');
    Route::post('{organization}/events/{id}/duplicate', [EventController::class, 'duplicate'])->name('events.duplicate');
    Route::post('{organization}/events', [EventController::class, 'store'])->name('events.store');
    Route::get('{organization}/edit/{id}', [EventController::class, 'edit'])->name('events.edit');
    Route::post('{organization}/update/{id}', [EventController::class, 'update'])->name('events.update');
    Route::post('{organization}/notification/{id}', [EventController::class, 'notification'])->name('events.notification');
    Route::post('event/pdf-upload/{id}', [EventController::class, 'pdfUpload'])->name('events.pdf_upload');
    Route::get('event/fetch-pdf-files/{id}', [EventController::class, 'getPdfFiles'])->name('events.fetch_pdf_files');
    Route::get('event/fetch-purchasers/{id}', [EventController::class, 'getPurchasers'])->name('events.fetch_purchasers');
    Route::get('event/pdf_remove', [EventController::class, 'pdf_remove']);
    Route::get('event/video_remove', [EventController::class, 'video_remove']);
    Route::get('/destroy/{id}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/event/{event}/inviteCamereCrew', [EventController::class, 'inviteCamereCrew'])->name('events.inviteCamereCrew');
    Route::get('/event/getEventPurchasers', [EventController::class, 'getEventPurchasers'])->name('event.getPurchasers');

    Route::get('sales/{event}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('sales/{event}/exportcsv', [SalesController::class, 'exportcsv'])->name('sales.exportcsv');

    Route::get('/tickets', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/tickets/{event_id}', [VoucherController::class, 'show'])->name('vouchers.show');
    Route::get('/tickets/download/{voucher}', [VoucherController::class, 'download'])->name('voucher.download');
    Route::get('/tickets/preview/{voucher}', [VoucherController::class, 'preview'])->name('voucher.preview');
    Route::get('/tickets/preview/map/{voucher}', [VoucherController::class, 'mapPreview'])->name('voucher.map.preview');
    Route::get('/tickets/{voucher}/refund', [VoucherController::class, 'refund'])->name('voucher.refund');
    Route::get('tickets/destroy/{id}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    Route::get('/sales/hide/{voucher}', [VoucherController::class, 'hideSales'])->name('voucher.hideSales');
    Route::get('/sales/show/{voucher}', [VoucherController::class, 'showSales'])->name('voucher.showSales');

    Route::get('location/{event}', [LocationController::class, 'edit'])->name('location');
    Route::patch('location/{event}', [LocationController::class, 'update'])->name('location.update');
    Route::delete('location/{event}', [LocationController::class, 'destroy'])->name('location.destroy');

    Route::post('follow', [FollowController::class, 'store']);
    Route::get('/send_feedback_mail', [EventController::class, 'sendFeedbackMail'])->name('event.feedback_mail');
    Route::get('/leave_feedback', [EventController::class, 'leave_feedback'])->name('event.leave_feedback');
    Route::post('save_feedback', [EventController::class, 'saveFeedback'])->name('feedback.update');
    Route::get('/event_feedbacks', [EventController::class, 'eventFeedbacks'])->name('event.event_feedbacks');

    Route::get('{organization}/{id}/stream', [TicketController::class, 'stream'])->name('tickets.stream');

    Route::post('stream', [CallAPIController::class, 'createNewStream'])->name('stream.create');
    Route::post('deleteStream', [CallAPIController::class, 'deleteStream'])->name('stream.delete');

    Route::post('meeting', [CallAPIController::class, 'createNewMeeting'])->name('meeting.create');
    Route::post('deleteMeeting', [CallAPIController::class, 'deleteMeeting'])->name('meeting.delete');

    Route::post('/organization/{organization}/subscribe', [OrganizationController::class, 'subscribe'])->name('organization.subscribe');
    Route::post('/organization/{organization}/unsubscribe', [OrganizationController::class, 'unsubscribe'])->name('organization.unsubscribe');

    Route::get('promo/send/{id}', [OrganizationController::class, 'sendPromo']);
});

Route::group(['middleware' => 'admin'], function () {
    Route::get('organization/{id}/edit/ad', [OrganizationAdController::class, 'edit'])->name('organization.ad.edit');
    Route::post('organization/{id}/store/ad', [OrganizationAdController::class, 'store'])->name('organization.ad.store');
    Route::get('event/{id}/edit/ad', [EventAdController::class, 'edit'])->name('events.ad.edit');
    Route::post('event/{id}/store/ad', [EventAdController::class, 'store'])->name('events.ad.store');
});

Route::get('/decide', [RegisterController::class, 'decide'])->name('decide');

Route::get('/checkout', [CheckoutController::class, 'decide'])->name('checkout.decide');
Route::get('/checkout/{type}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/charge', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/charge/poynt', [CheckoutController::class, 'chargePoynt'])->name('checkout.poynt.charge');

Route::get('/events/{category?}', [EventController::class, 'index'])->name('events.index');
Route::get('/organizations/{category?}', [OrganizationController::class, 'index'])->name('organization.index');
Route::get('{organization}/{title}_{event}', [EventController::class, 'show'])->name('events.show');
Route::get('{organization}', [OrganizationController::class, 'show'])->name('organization.show');

Route::get('/event/{ticket}/live/token', [EventController::class, 'getAgoraAccessToken'])->name('events.getAgoraAccessToken');

Route::get('/unsubscribe/{token}', [ProfileController::class, 'unsubscribe'])->name('profile.unsubscribe');

Route::get('{organization}/{event}/advertise', [EventController::class, 'advertise'])->name('events.advertise');

//promo code
Route::get('/organization/{organization}/affiliate/{promoCode}', [OrganizationController::class, 'affiliate'])->name('organization.affiliate');
Route::post('/organization/{organization}/affiliate/{promoCode}', [OrganizationController::class, 'affiliateSubmit'])->name('organization.affiliate.submit');

//cart
Route::get('cart/checkout', [CartController::class, 'cartCheckout'])->name('cart.checkout');
Route::post('cart/checkout/promo-apply', [CartController::class, 'cartCheckoutPromoApply'])->name('cart.checkout.promo.apply');
Route::post('cart/charge', [CheckoutController::class, 'cartCheckout'])->name('cart.charge');
Route::post('cart', [CartController::class, 'addToCart'])->name('cart.store');
Route::patch('cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::put('cart', [CartController::class, 'removeCart'])->name('cart.remove');
Route::delete('cart', [CartController::class, 'clearAllCart'])->name('cart.clear');

//webhook subscription
Route::post('/paypal/webhook', [OrganizationController::class, 'paypal_webhook']);
