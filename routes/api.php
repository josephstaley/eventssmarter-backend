<?php

use App\Http\Controllers\API\BannerAPIController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\CallAPIController;
use App\Http\Controllers\LiveStream\LiveStreamAPIController;
use App\Http\Controllers\NFT\NFTController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\API\EventAPIController;
use App\Http\Controllers\API\CheckoutAPIController;
use App\Http\Controllers\API\OrganizationAPIController;
use App\Http\Controllers\API\StreamAPIController;
use App\Http\Controllers\API\VoucherAPIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:api')->get('/user', [ApiController::class, 'user']);

Route::get('search', [ApiController::class, 'search']);

Route::get('email', [ApiController::class, 'email']);

Route::any('login', [ApiController::class, 'login']);

Route::post('userlogin', [ApiController::class, 'userlogin']);

Route::get('events/user/{user}', [ApiController::class, 'events']);

Route::post('event/{event}', [ApiController::class, 'event']);


/*
|--------------------------------------------------------------------------
| API Routes For call
|--------------------------------------------------------------------------
|
*/
Route::post('call/login', [CallAPIController::class, 'login']);
Route::post('live/login', [CallAPIController::class, 'liveStreamLogin']);
Route::post('stream/login', [CallAPIController::class, 'StreamLogin']);
Route::post('meeting/login', [CallAPIController::class, 'MeetingLogin']);

Route::post('stream/invite/login', [CallAPIController::class, 'StreamInviteLogin']);
Route::post('live/invite/login', [CallAPIController::class, 'LiveInviteLogin']);
Route::post('call/invite/login', [CallAPIController::class, 'CallInviteLogin']);
Route::post('meeting/invite/login', [CallAPIController::class, 'MeetingInviteLogin']);

Route::post('live/openvidu_token', [CallAPIController::class, 'generateOpenviduToken']);


/*
|--------------------------------------------------------------------------
| API Routes For Agora
|--------------------------------------------------------------------------
|
*/
Route::post('live/init', [LiveStreamAPIController::class, 'liveStreamInit']);
Route::post('live/invite', [LiveStreamAPIController::class, 'liveStreamInvite']);

/*
|--------------------------------------------------------------------------
| API Routes For NFT
|--------------------------------------------------------------------------
|
*/
Route::post('nft', [NFTController::class, 'store']);
Route::get('nft/{id}', [NFTController::class, 'getNFT']);

// crypto payment verify
Route::post('/crypto/paymentVerify', [CryptoController::class, 'paymentVerify'])->name('crypto.paymentVerify');

/*
|--------------------------------------------------------------------------
| API Routes For Promo
|--------------------------------------------------------------------------
|
*/
Route::get('organization/generate_promo', [OrganizationController::class, 'generatePromoCode']);
Route::post('promo/check/{code}', [OrganizationController::class, 'checkPromoCode']);
Route::delete('promo/delete/{code}', [OrganizationController::class, 'deletePromo']);


/*
|--------------------------------------------------------------------------
| API Routes For New FRONTEND
|--------------------------------------------------------------------------
|
*/

Route::get('health', [UserAPIController::class, 'healthCheck']);

Route::group(['prefix' => 'v1'], function () {
    Route::post('sign_in', [UserAPIController::class, 'sign_in']);
    Route::post('sign_up', [UserAPIController::class, 'sign_up']);
    Route::post('sign_out', [UserAPIController::class, 'sign_out']);
    Route::post('forgot_password', [UserAPIController::class, 'forgot_password']);
    Route::post('reset_password', [UserAPIController::class, 'reset_password']);

    Route::get('upcoming_events', [EventAPIController::class, 'upcoming_events']);
    Route::get('events', [EventAPIController::class, 'events']);
    Route::get('events/{category?}', [EventAPIController::class, 'eventsByCategory']);
    Route::get('organizations/{category?}', [OrganizationAPIController::class, 'index']);
    Route::get('organization/{org_slug?}', [OrganizationAPIController::class, 'organizationDetail']);
    Route::get('streams', [StreamAPIController::class, 'streams']);
    Route::get('{organization}/{title}_{event}', [EventAPIController::class, 'show']);

    Route::get('/preview-seat/event/{event}/voucher/{voucher}', [EventAPIController::class, 'seatVoucherPreview']);


    Route::post('ai/summarize_text', [StreamAPIController::class, 'summarizeText']);
    Route::post('ai/summarize_text/send_email', [StreamAPIController::class, 'sendEmailSummarizedText']);

    Route::post('checkout/paypal/verify', [CheckoutAPIController::class, 'paypalTransactionVerify']);
    Route::post('checkout/paypal/subscription/verify', [CheckoutAPIController::class, 'paypalSubscriptionTransactionVerify']);
    Route::post('checkout/stripe/verify', [CheckoutAPIController::class, 'stripeTransactionVerify']);
    Route::post('checkout/{event:id}', [CheckoutAPIController::class, 'checkout']);
    Route::post('checkout/promo_code/apply', [CheckoutAPIController::class, 'cartCheckoutPromoApply']);

    Route::group(['middleware' => 'auth:sanctum'], function () {

        Route::get('vouchers', [VoucherAPIController::class, 'getVouchers']);

        Route::get('stream', [StreamAPIController::class, 'getMyStreams']);
        Route::post('stream', [StreamAPIController::class, 'createNewStream']);
        Route::delete('stream', [StreamAPIController::class, 'deleteStream']);

        Route::get('meeting', [StreamAPIController::class, 'getMyMeetings']);
        Route::post('meeting', [StreamAPIController::class, 'createNewMeeting']);
        Route::delete('meeting', [StreamAPIController::class, 'deleteMeeting']);

        // CRUD organization
        Route::get('me/organization', [OrganizationAPIController::class, 'myOrganizations']);
        Route::post('me/organization', [OrganizationAPIController::class, 'createOrganization']);
        Route::patch('me/organization/{organization:id}', [OrganizationAPIController::class, 'updateOrganization']);
        Route::delete('me/organization/{organization:id}', [OrganizationAPIController::class, 'deleteOrganization']);

        // CRUD event
        Route::get('me/event', [EventAPIController::class, 'myEvents']);
        Route::post('me/event', [EventAPIController::class, 'createEvent']);
        Route::patch('me/event/{event:id}', [EventAPIController::class, 'updateEvent']);
        Route::delete('me/event/{event:id}', [EventAPIController::class, 'deleteEvent']);
        Route::post('me/event/{event:id}/duplicate', [EventAPIController::class, 'duplicateEvent']);
        // generate Event description by ai
        Route::post('me/event/generate_description', [EventAPIController::class, 'generateEventDescription']);
        // CRUD ticket
        Route::post('me/event/{event:id}/ticket', [EventAPIController::class, 'createTicket']);
        Route::patch('me/event/{event:id}/ticket/{ticket:id}', [EventAPIController::class, 'updateTicket']);
        Route::delete('me/event/{event:id}/ticket/{ticket:id}', [EventAPIController::class, 'deleteTicket']);
        Route::patch('me/event/{event:id}/tickets/sort', [EventAPIController::class, 'sortTicket']);

        // Sales
        Route::get('me/sales/{event:id}', [VoucherAPIController::class, 'getSalesByEvent']);
        Route::get('me/sales/{event:id}/exportcsv', [VoucherAPIController::class, 'exportcsv']);

        //Profile
        Route::patch('me/profile', [UserAPIController::class, 'updateProfile']);
        Route::patch('me/change_password', [UserAPIController::class, 'updatePassword']);

        //Promo Codes
        Route::get('me/organization/{organization:id}/promo', [OrganizationAPIController::class, 'getPromo']);
        Route::post('me/organization/{organization:id}/promo', [OrganizationAPIController::class, 'createPromo']);
        Route::patch('me/organization/{organization:id}/promo/{promo}', [OrganizationAPIController::class, 'updatePromo']);
        Route::delete('me/organization/{organization:id}/promo/{promo}', [OrganizationAPIController::class, 'deletePromo']);
        Route::get('me/organization/{organization:id}/promo/{promo}/email', [OrganizationAPIController::class, 'sendPromoBankEmail']);

        //Send notification to event buyers
        Route::post('me/event/{event:id}/notification', [EventAPIController::class, 'sendNotification']);
        Route::post('me/all/notification', [EventAPIController::class, 'sendNotificationToAll']);
        //Send Camera Production Team Email
        Route::post('me/ticket/{ticket:id}/invite_camera_team', [EventAPIController::class, 'sendInviteCameraTeamEmail']);
        //Watch Stream
        Route::get('stream/{ticket:id}', [StreamAPIController::class, 'getStreamInformation']);

        //event members CRUD
        Route::get('me/event/{event:id}/members', [EventAPIController::class, 'getMembers']);
        Route::post('me/event/{event:id}/member', [EventAPIController::class, 'addMember']);
        Route::delete('me/event/{event:id}/member/{member_id}', [EventAPIController::class, 'deleteMember']);
        Route::patch('me/event/{event:id}/member', [EventAPIController::class, 'updateMemberRole']);
        Route::patch('me/event/{event:id}/member/leave', [EventAPIController::class, 'MemberLeave']);

        //Banner ADS
        Route::get('me/organization/{organization:id}/banners', [BannerAPIController::class, 'getOrganizationBanners']);
        Route::post('me/organization/{organization:id}/banners', [BannerAPIController::class, 'addOrganizationBanner']);
        Route::delete('me/organization/{organization:id}/banners/{banner:id}', [BannerAPIController::class, 'deleteOrganizationBanner']);

        Route::get('me/event/{event:id}/banners', [BannerAPIController::class, 'getEventBanners']);
        Route::post('me/event/{event:id}/banners', [BannerAPIController::class, 'addEventBanner']);
        Route::delete('me/event/{event:id}/banners/{banner:id}', [BannerAPIController::class, 'deleteEventBanner']);

        //Subscribe
        Route::post('organization/{organization:id}/subscribe', [UserAPIController::class, 'subscribe'])->name('organization.subscribe');
        Route::post('organization/{organization:id}/unsubscribe', [UserAPIController::class, 'unsubscribe'])->name('organization.unsubscribe');
    });

    Route::patch('me/event/{event:id}/member/accept', [EventAPIController::class, 'MemberAccept']);
    Route::patch('me/event/{event:id}/member/deny', [EventAPIController::class, 'MemberDeny']);
});
