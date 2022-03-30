<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//Token Routes
//Route::group(['middleware'=>['CheckApiToken']],function (){
    
    Route::post('change-password', 'Users_Controller@change_password');
    Route::post('check-verification-status', 'Users_Controller@check_verification_status');
    Route::post('send-verification-code', 'Users_Controller@send_verification_code');
    Route::post('verify-verification-code', 'Users_Controller@verify_verification_code');
    Route::post('document-verification', 'Users_Controller@document_verification');
    Route::post('check-step-verification', 'Users_Controller@check_step_verification');

    Route::post('toggle-wishlist', 'ProductsController@toggle_wishlist');
    Route::post('get-user-wishlist', 'ProductsController@get_user_wishlist');
    
    Route::post('my-reviews', 'Users_Controller@my_reviews');
    
    //Trip
    Route::post('create-trip', 'TripsController@create_trip');
    Route::post('my-trips', 'TripsController@my_trips');
    Route::post('edit-trip', 'TripsController@edit_trip');
    Route::post('update-trip', 'TripsController@update_trip');
    Route::post('delete-trip', 'TripsController@delete_trip');
    Route::post('completed-trips', 'TripsController@completed_trips');
    Route::post('get-trips-for-shipment', 'TripsController@get_trips_for_shipment');
    Route::post('get-requested-trips', 'TripsController@get_requested_trips');
    

    //Shipment
    Route::post('create-shipment', 'ShipmentController@create_shipment');
    Route::post('get-shipments', 'ShipmentController@get_shipments');
    Route::post('get-shipment-details', 'ShipmentController@get_shipment_details');
    Route::post('my-shipments', 'ShipmentController@my_shipments');
    Route::get('trending-shipments', 'ShipmentController@trending_shipments');
    Route::post('delivered-shipments', 'ShipmentController@delivered_shipments');
    Route::post('get-shipments-for-trip', 'ShipmentController@get_shipments_for_trip');
    Route::post('get-requested-shipments', 'ShipmentController@get_requested_shipments');
    Route::post('delete-cancel-shipment', 'ShipmentController@delete_cancel_shipment');
    Route::post('delete-shipment-product', 'ShipmentController@delete_shipment_product');
    Route::post('edit-shipment-product', 'ShipmentController@edit_shipment_product');
    Route::post('update-shipment-product', 'ShipmentController@update_shipment_product');

    //Deal
    Route::post('add-request', 'DealsController@add_request');
    Route::post('get-deals', 'DealsController@get_deals');
    Route::post('get-deal-detail', 'DealsController@get_deal_detail');
    Route::post('change-deal-status', 'DealsController@change_deal_status');
    Route::post('cancel-deal', 'DealsController@cancel_deal');
    Route::post('cancel-request', 'DealsController@cancel_request');

    //Chat
    Route::post('get-chat-lists', 'ChatController@get_chat_lists');

    //Tracking
    Route::post('get-tracking-orders', 'DealsController@get_tracking_orders');
    Route::post('track-order', 'DealsController@track_order');
    Route::post('change-order-status', 'DealsController@change_order_status');
    Route::post('order-otp-generate', 'DealsController@order_otp_generate');
    Route::post('order-otp-submit', 'DealsController@order_otp_submit');
    
    Route::post('send-trip-delay-details', 'DealsController@send_trip_delay_details');

    //Settings
    Route::get('get-size-settings', 'Users_Controller@get_size_settings');
    Route::post('get-settings', 'Users_Controller@get_settings');
    Route::post('update-settings', 'Users_Controller@update_settings');

    //Contact Us
    Route::post('contact-us', 'ApiController@contact_us');
    Route::post('report-problem', 'ApiController@report_problem');

    //Notifications
    Route::post('get-notifications', 'ApiController@get_notifications');
    Route::post('get-notification-count', 'ApiController@get_notification_count');
    Route::post('seen-notification', 'ApiController@seen_notification');
    Route::post('delete-notification', 'ApiController@delete_notification');

    //Message
    Route::post('get-unread-messages-count', 'ChatController@get_unread_messages_count');
    Route::post('get-old-messages', 'ChatController@get_old_messages');
    Route::post('marked-as-seen-msg', 'ChatController@marked_as_seen_msg');

    //Payment
    Route::post('payment-history', 'ApiController@payment_history');
    Route::post('save-payment', 'ApiController@save_payment');
    
    Route::post('add-withdrawal-request', 'ApiController@add_withdrawal_request');
    
    //Rating
    Route::post('rate-user', 'ApiController@rate_user');
    
    
    Route::post('change-created-status', 'ShipmentController@change_created_status');
    
    Route::get('get-categories', 'ProductsController@get_categories');
    
    //Products
    Route::post('get-products', 'ProductsController@get_products');
    Route::post('get-product-detail', 'ProductsController@get_product_detail');
    
    
    //Trips
    Route::post('get-trips', 'TripsController@get_trips');
    Route::post('get-trip-details', 'TripsController@get_trip_details');
    
    //City / Country
    Route::post('get-cities', 'AdminController@get_cities');
    
    Route::get('get-country-list', 'AdminController@get_country_list');
    Route::post('get-stateCountryByCity', 'AdminController@stateCountryByCity');

    Route::post('get-user-profile', 'Users_Controller@get_user_profile');
    Route::post('update-user-profile', 'Users_Controller@update_user_profile');
    Route::post('update-user-profile-image', 'Users_Controller@update_user_profile_image');
    
    Route::post('get-otp', 'Users_Controller@get_otp');
    Route::post('verify-otp', 'Users_Controller@verify_otp');
    
    Route::post('save-feedback', 'Users_Controller@save_feedback');
    
    Route::post('check-new-user', 'Users_Controller@check_new_user');
    
    Route::post('set-withdrawal-pin', 'Users_Controller@set_withdrawal_pin');
    Route::post('get-withdrawal-pin', 'Users_Controller@get_withdrawal_pin');
    Route::post('forgot-pin', 'Users_Controller@forgot_pin');
    
    
//});

//Get Page
Route::post('get-page', 'ApiController@get_page');

Route::post('get-how-it-works', 'ApiController@get_how_it_works');
Route::post('get-faq', 'ApiController@get_faq');

Route::post('get-countries', 'AdminController@get_countries');
Route::post('send-verification-mail', 'Users_Controller@send_verification_mail');
    
Route::post('social-media-verification', 'Users_Controller@social_media_verification');

Route::post('django-order-otp-generate', 'DealsController@order_otp_generate');
Route::post('django-order-otp-verify', 'DealsController@order_otp_verify');

Route::post('django-send-return-shipment-otp', 'DealsController@send_return_shipment_otp');
Route::post('django-shipment-return-otp-verify', 'DealsController@return_shipment_otp_verify');

Route::post('send-chat-notification', 'ChatController@send_chat_notification');

Route::get('test', 'Users_Controller@test');



//User
Route::post('user-registration', 'Users_Controller@user_registration');
Route::post('user-login', 'Users_Controller@user_login');
Route::post('forgot-password', 'Users_Controller@forgot_password');


//============== For Django =================
Route::post('upload-file', 'ApiController@upload_file');
Route::post('delete-file', 'ApiController@delete_file');
Route::post('send-document-update-notification', 'ApiController@send_document_update_notification');
Route::post('send-email-update-notification', 'ApiController@send_email_update_notification');

Route::post('cancel-deal-by-admin', 'ApiController@cancel_deal_by_admin');
Route::post('send-report-problem-reply-mail', 'ApiController@send_report_problem_reply_mail');


//=============NotificationController=============
Route::post('/testNotification', 'NotificationController@testNotification');

Route::fallback(function(){
    return response()->json([
        'status'=>false,
        'status_code'=>404,
        'message' => 'Route Not Found.'], 404);
});