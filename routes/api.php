<?php
/**
 * File name: api.php
 * Last modified: 2020.10.31 at 12:40:48
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

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

use App\Http\Controllers\API\Manager\MerchantRequestAPIController;
use App\Http\Controllers\API\OtpController;
use App\Http\Controllers\API\RazorpayController;
use Illuminate\Support\Facades\Route;

Route::prefix('driver')->group(function () {
//    Route::post('login', 'API\Driver\UserAPIController@login');
//    Route::post('register', 'API\Driver\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\Driver\UserAPIController@user');
    Route::get('logout', 'API\Driver\UserAPIController@logout');
    Route::get('settings', 'API\Driver\UserAPIController@settings');
    Route::post('otp/send', [OtpController::class, 'send']);
    Route::post('otp/verify', [OtpController::class, 'verify']);
    Route::post('login', 'API\UserAPIController@driverLogin');
});



Route::prefix('manager')->group(function () {
    Route::post('login', 'API\Manager\UserAPIController@login');
    Route::post('register', 'API\Manager\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\Manager\UserAPIController@user');
    Route::get('logout', 'API\Manager\UserAPIController@logout');
    Route::get('settings', 'API\Manager\UserAPIController@settings');
    Route::post('otp/send', [OtpController::class, 'send']);
    Route::post('otp/verify', [OtpController::class, 'verify']);
});

Route::post('otp/send', [OtpController::class, 'send']);
Route::post('otp/verify', [OtpController::class, 'verify']);
Route::post('merchant-requests', [MerchantRequestAPIController::class, 'store']);

Route::post('login', 'API\UserAPIController@login');
Route::post('register', 'API\UserAPIController@register');
Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
Route::get('user', 'API\UserAPIController@user');
Route::get('logout', 'API\UserAPIController@logout');
Route::get('settings', 'API\UserAPIController@settings');

Route::resource('cities', 'API\CityAPIController');
Route::resource('areas', 'API\CircleAPIController');
Route::resource('terms-and-conditions', 'API\TermsAndConditionsAPIController');

Route::resource('sub-categories', 'API\SubCategoryAPIController');

Route::resource('t-shirt-sizes', 'API\TShirtSizeAPIController');

Route::resource('fields', 'API\FieldAPIController');
Route::resource('categories', 'API\CategoryAPIController')->except(['edit','update','destroy']);
Route::resource('markets', 'API\MarketAPIController');

Route::resource('faq_categories', 'API\FaqCategoryAPIController');
Route::get('products/autocomplete', 'API\ProductAPIController@autocomplete');
Route::get('products/categories', 'API\ProductAPIController@categories');
Route::resource('products', 'API\ProductAPIController');
Route::resource('galleries', 'API\GalleryAPIController');
Route::resource('product_reviews', 'API\ProductReviewAPIController');

Route::resource('faqs', 'API\FaqAPIController');
Route::resource('market_reviews', 'API\MarketReviewAPIController');
Route::resource('currencies', 'API\CurrencyAPIController');
Route::resource('slides', 'API\SlideAPIController')->except([
    'show'
]);

Route::resource('attributes', 'API\AttributeAPIController');
Route::resource('attributes_options', 'API\AttributeOptionAPIController');
Route::get('product_varient/attributes_options/{id}', 'API\ProductAPIController@variantAttributeOption');
Route::get('order_requests/index', 'API\OrderRequestAPIController@index');
Route::post('order_requests/store', 'API\OrderRequestAPIController@store');

Route::resource('delivery_types', 'API\DeliveryTypesAPIController');
Route::resource('sector_delivery_types', 'API\SectorDeliveryTypeAPIController');
Route::resource('payment_methods', 'API\PaymentMethodAPIController');
Route::resource('vendor_payment_methods', 'API\VendorPaymentMethodAPIController');
Route::resource('packages', 'API\PackageAPIController');

Route::resource('pick-up-vehicles', 'API\PickUpVehicleAPIController');
Route::middleware('auth:sanctum')->group(function () {
    Route::group(['middleware' => ['role:driver']], function () {
        Route::prefix('driver')->group(function () {
            Route::resource('complaints', 'API\Driver\ComplaintAPIController');
            Route::post('checkin-checkout', 'API\Driver\CheckInCheckOutAPIController@checkInCheckOut');
            Route::get('checkin-checkout', 'API\Driver\CheckInCheckOutAPIController@index');
            Route::resource('orders', 'API\Driver\OrderAPIController');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::post('users/{id}', 'API\UserAPIController@update');
            Route::resource('faq_categories', 'API\FaqCategoryAPIController');
            Route::resource('faqs', 'API\FaqAPIController');
            Route::get('transactions', 'API\Driver\TransactionAPIController@index');
            Route::get('profile', 'API\Driver\ProfileAPIController@index');
            Route::post('driver-payout-request', 'API\Driver\ProfileAPIController@payOutRequest');
            Route::post('locality-vehicle-details', 'API\Driver\ProfileAPIController@updateLocalityDetails');
            Route::post('personal-details', 'API\Driver\ProfileAPIController@updatePersonalDetails');
            Route::post('bank-details', 'API\Driver\ProfileAPIController@updateBankDetails');
            Route::post('upload-profile-image', 'API\Driver\ProfileAPIController@updateProfileImage');
            Route::post('update-profile-image', 'API\Driver\ProfileAPIController@changeProfileImage');
            Route::resource('change-order-status', 'API\Driver\OrderStatusChangeController');
            Route::post('accept-order', 'API\Driver\DriverApproveOrderController@update');
            Route::get('driver-balance', 'API\Driver\TransactionAPIController@getDriverBalance');
        });
    });
    Route::group(['middleware' => ['role:vendor_owner']], function () {
        Route::prefix('manager')->group(function () {
            Route::post('users/{id}', 'API\UserAPIController@update');
            Route::post('profile-update', 'API\Manager\UserAPIController@updateProfile');
            Route::get('users/drivers_of_market/{id}', 'API\Manager\UserAPIController@driversOfMarket');
            Route::get('dashboard/{id}', 'API\DashboardAPIController@manager');
            Route::resource('markets', 'API\Manager\MarketAPIController');
            Route::resource('products', 'API\Manager\ProductAPIController');
            Route::resource('orders', 'API\Manager\OrderAPIController');
            Route::resource('order-requests', 'API\Manager\OrderRequestAPIController');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::get('market/close', 'API\Manager\MarketAPIController@openOrCloseMarket');
            Route::post('update-profile-image', 'API\Manager\UserAPIController@updateProfileImage');
            Route::post('product/enable-disable', 'API\Manager\ProductAPIController@enableOrDisable');
            Route::get('order/cancel', 'API\Manager\OrderAPIController@cancelOrder');
            Route::get('cancel-package-item', 'API\Manager\OrderAPIController@cancelPackageItem');
            Route::resource('packages', 'API\Manager\PackageAPIController');
            Route::resource('payment-transactions', 'API\Manager\PaymentTransactionAPIController');
            Route::resource('payout-requests', 'API\Manager\PayoutRequestAPIController');
            Route::post('order-approve', 'API\Manager\OrderAPIController@approveOrder');
            Route::get('get-total-earnings-and-balance', 'API\Manager\UserAPIController@balanceAndEarnings');
        });
    });
    Route::post('users/{id}', 'API\UserAPIController@update');

    Route::post('signature-verifications', [RazorpayController::class,'verifySignature']);

    Route::resource('order_statuses', 'API\OrderStatusAPIController');
    Route::resource('return-requests', 'API\ReturnRequestsAPIController');
    Route::resource('user-product-reviews', 'API\ProductUserReviewAPIController');
    Route::resource('user-market-reviews', 'API\MarketUserReviewAPIController');
    Route::resource('user-driver-reviews', 'API\UserDriverReviewAPIController');
    Route::resource('driver-reviews', 'API\Driver\DriverReviewAPIController');

    Route::get('payments/byMonth', 'API\PaymentAPIController@byMonth')->name('payments.byMonth');

    Route::resource('payments', 'API\PaymentAPIController');
    Route::resource('complaints', 'API\ComplaintAPIController');

    Route::get('favorites/exist', 'API\FavoriteAPIController@exist');
    Route::resource('favorites', 'API\FavoriteAPIController');

    Route::resource('orders', 'API\OrderAPIController');

    Route::resource('product_orders', 'API\ProductOrderAPIController');

    Route::resource('notifications', 'API\NotificationAPIController');

    Route::get('carts/count', 'API\CartAPIController@count')->name('carts.count');

    Route::resource('carts', 'API\CartAPIController');

    Route::post('carts/remove-from-cart', 'API\CartAPIController@remove');


    Route::resource('delivery_addresses', 'API\DeliveryAddressAPIController');

    Route::resource('drivers', 'API\DriverAPIController');

    Route::resource('earnings', 'API\EarningAPIController');

    Route::resource('driversPayouts', 'API\DriversPayoutAPIController');

    Route::resource('marketsPayouts', 'API\MarketsPayoutAPIController');
    Route::resource('my-packages', 'API\MyPackageAPIController');
    Route::get('user-wallet', 'API\UserWalletController@userWalletAmount');
    Route::get('user-wallet-transactions', 'API\UserWalletController@userWalletHistories');
    Route::get('cancel-subscriptions', 'API\MyPackageAPIController@cancelOrder');
    Route::get('cancel-package-item', 'API\MyPackageAPIController@cancelPackageItem');

    Route::resource('coupons', 'API\CouponAPIController')->except([
        'show'
    ]);
    Route::post('product_first_order', 'API\OrderAPIController@productFirstOrder');
    Route::post('uploads/store', 'API\UploadAPIController@store');
    Route::post('profile-image-upload', 'API\UserAPIController@profileImageUpload');
    Route::post('uploads/clear', 'API\UploadAPIController@clear');
    Route::resource('pick-up-order-requests', 'API\PickUpDeliveryOrderRequestAPIController');
    Route::resource('pick-up-delivery-orders', 'API\PickUpDeliveryOrderAPIController');
    Route::resource('order-request-orders', 'API\OrderRequestOrderAPIController');
    Route::resource('slots', 'API\SlotAPIController');
    Route::get('pickup-delivery-avaliable', 'API\PickUpDeliveryOrderAPIController@checkAvailability');
    Route::get('order-coupon', 'API\CouponAPIController@validCoupon');

});

