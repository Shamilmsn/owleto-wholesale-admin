<?php
/**
 * File name: web.php
 * Last modified: 2020.06.07 at 07:02:57
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 *
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('login/{service}', 'Auth\LoginController@redirectToProvider');
Route::get('login/{service}/callback', 'Auth\LoginController@handleProviderCallback');
Auth::routes();

Route::get('payments/failed', 'PayPalController@index')->name('payments.failed');
Route::get('payments/razorpay/checkout', 'RazorPayController@checkout');
Route::post('payments/razorpay/pay-success/{userId}/{deliveryAddressId?}/{couponCode?}', 'RazorPayController@paySuccess');
Route::get('payments/razorpay', 'RazorPayController@index');

Route::get('payments/paypal/express-checkout', 'PayPalController@getExpressCheckout')->name('paypal.express-checkout');
Route::get('payments/paypal/express-checkout-success', 'PayPalController@getExpressCheckoutSuccess');
Route::get('payments/paypal', 'PayPalController@index')->name('paypal.index');

Route::get('firebase/sw-js','AppSettingController@initFirebase');


Route::get('storage/app/public/{id}/{conversion}/{filename?}', 'UploadController@storage');
Route::middleware('auth')->group(function () {
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('/', 'DashboardController@index')->name('dashboard');

    Route::post('uploads/store', 'UploadController@store')->name('medias.create');
    Route::get('users/profile', 'UserController@profile')->name('users.profile');
    Route::get('users/{id}/profile', 'UserController@profile');
    Route::post('users/remove-media', 'UserController@removeMedia');
    Route::resource('users', 'UserController');
    Route::resource('merchant-requests', 'MerchantRequestController');
    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::group(['middleware' => ['permission:medias']], function () {
        Route::get('uploads/all/{collection?}', 'UploadController@all');
        Route::get('uploads/collectionsNames', 'UploadController@collectionsNames');
        Route::post('uploads/clear', 'UploadController@clear')->name('medias.delete');
        Route::get('medias', 'UploadController@index')->name('medias');
        Route::get('uploads/clear-all', 'UploadController@clearAll');
    });

    Route::group(['middleware' => ['permission:permissions.index']], function () {
        Route::get('permissions/role-has-permission', 'PermissionController@roleHasPermission');
        Route::get('permissions/refresh-permissions', 'PermissionController@refreshPermissions');
    });
    Route::group(['middleware' => ['permission:permissions.index']], function () {
        Route::post('permissions/give-permission-to-role', 'PermissionController@givePermissionToRole');
        Route::post('permissions/revoke-permission-to-role', 'PermissionController@revokePermissionToRole');
    });

    Route::group(['middleware' => ['permission:app-settings']], function () {
        Route::prefix('settings')->group(function () {
            Route::resource('permissions', 'PermissionController');
            Route::resource('roles', 'RoleController');
            Route::resource('customFields', 'CustomFieldController');
            Route::resource('currencies', 'CurrencyController')->except([
                'show'
            ]);
            Route::get('users/login-as-user/{id}', 'UserController@loginAsUser')->name('users.login-as-user');
            Route::patch('update', 'AppSettingController@update');
            Route::patch('translate', 'AppSettingController@translate');
            Route::get('sync-translation', 'AppSettingController@syncTranslation');
            Route::get('clear-cache', 'AppSettingController@clearCache');
            Route::get('/{type?}/{tab?}', 'AppSettingController@index')
                ->where('type', '[A-Za-z]*')->where('tab', '[A-Za-z]*')->name('app-settings');
        });
    });

    Route::post('fields/remove-media','FieldController@removeMedia');
    Route::resource('fields', 'FieldController')->except([
        'show'
    ]);

    Route::post('markets/remove-media', 'MarketController@removeMedia');
    Route::get('requestedMarkets', 'MarketController@requestedMarkets')->name('requestedMarkets.index'); //adeed
    Route::resource('markets', 'MarketController')->except([
        'show'
    ]);

    Route::get('markets/{id}', 'MarketController@show');

    Route::post('categories/remove-media', 'CategoryController@removeMedia');
    Route::resource('categories', 'CategoryController')->except([
        'show'
    ]);

    Route::resource('faqCategories', 'FaqCategoryController')->except([
        'show'
    ]);

    Route::resource('orderStatuses', 'OrderStatusController')->except([
        'create', 'store', 'destroy'
    ]);

    Route::resource('orderRequests', 'OrderRequestController');
    Route::resource('orderRequestCarts', 'OrderRequestCartController');
    Route::get('orderRequests/cart', 'DeliveryTypeController@cart')->name('orderRequests.cart');
    Route::resource('deliveryTypes', 'DeliveryTypeController');

    Route::post('products/remove-media', 'ProductController@removeMedia');
    Route::get('products/get-attribute-options-by-attributes', 'ProductController@getAttributeOptions');
    Route::resource('products', 'ProductController')->except([
        'show'
    ]);

    Route::post('galleries/remove-media', 'GalleryController@removeMedia');
    Route::resource('collected-cash', 'CashController')->only('store');
    Route::resource('galleries', 'GalleryController')->except([
        'show'
    ]);

    Route::resource('productReviews', 'ProductReviewController')->except([
        'show'
    ]);

    Route::resource('driver-reviews', 'DriverReviewController')->except([
        'show'
    ]);

    Route::post('options/remove-media', 'OptionController@removeMedia');


    Route::resource('payments', 'PaymentController')->except([
        'create', 'store','edit', 'destroy'
    ]);;

    Route::resource('faqs', 'FaqController')->except([
        'show'
    ]);
    Route::resource('marketReviews', 'MarketReviewController')->except([
        'show'
    ]);

    Route::resource('favorites', 'FavoriteController')->except([
        'show'
    ]);

    Route::resource('orders', 'OrderController');
    Route::resource('express-orders', 'ExpressOrderController');
    Route::resource('home-bakers-orders', 'HomeBakersOrderController');

    Route::resource('pickup-orders', 'SlotPickUpOrderController');
    Route::post('pickup-orders/assign-drivers',
        'SlotPickUpOrderController@assignDriver');
    Route::resource('deliver-orders', 'SlotDeliveryOrderController');
    Route::post('deliver-orders/assign-drivers',
        'SlotDeliveryOrderController@assignDriver');

    Route::resource('notifications', 'NotificationController')->except([
        'create', 'store', 'update','edit',
    ]);;

    Route::resource('carts', 'CartController')->except([
        'show','store','create'
    ]);
    Route::resource('deliveryAddresses', 'DeliveryAddressController')->except([
        'show'
    ]);

    Route::resource('drivers', 'DriverController');
    Route::resource('compliant', 'ComplaintController');

    Route::resource('earnings', 'EarningController')->except([
        'show','edit','update'
    ]);

    Route::resource('driversPayouts', 'DriversPayoutController')->except([
        'show','edit','update'
    ]);
    Route::post('driver-payouts', 'DriversPayoutController@driverPayoutStore')->name('driver-payout.store');

    Route::resource('marketsPayouts', 'MarketsPayoutController')->except([
        'show','edit','update'
    ]);

    Route::resource('optionGroups', 'OptionGroupController')->except([
        'show'
    ]);
    Route::resource('attributes', 'AttributeController')->except([
        'show'
    ]);
    Route::resource('terms', 'TermController');
    Route::resource('attributeOptions', 'AttributeOptionController')->except([
        'show'
    ]);

    Route::post('options/remove-media','OptionController@removeMedia');

    Route::resource('options', 'OptionController')->except([
        'show'
    ]);
    Route::resource('coupons', 'CouponController')->except([
        'show'
    ]);

    Route::resource('pick-up-vehicles', 'PickUpVehicleController');

    Route::resource('driver-requests', 'DriverRequestController');

    Route::resource('pickup-delivery-order-requests', 'PickUpDeliveryOrderRequestController');
    Route::post('pickup-delivery-order-request/status/rejected', 'PickUpDeliveryOrderRequestController@statusRejected')
        ->name('pick_up_order_requests.reject');

    Route::post('slides/remove-media','SlideController@removeMedia');
    Route::resource('slides', 'SlideController')->except([
        'show'
    ]);

    Route::resource('packages', 'SubscriptionPackageController');
    Route::resource('market-payout-requests', 'MarketPayoutRequestController');
    Route::resource('package-orders', 'PackageOrderController');
    Route::resource('market_transactions', 'TransactionController');
    Route::resource('driver_transactions', 'DriverTransactionController');
    Route::get('market_products/ajax/{id}', 'SubscriptionPackageController@market_products');
    Route::get('product/sub_categories/ajax/{id}', 'CategoryController@subCategories');
    Route::get('product/get-categories/ajax/{id}', 'CategoryController@getCategories');
    Route::post('market_categories/ajax', 'MarketController@market_categories');
    Route::post('check-email-unique','UserController@checkEmailUnique');
    Route::post('check-phone-unique','UserController@checkPhoneUnique');
    Route::post('temp-order-request/remove-media', 'OrderRequestController@removeMedia');
    Route::resource('temporary-order-requests', 'TemporaryOrderRequestController');
    Route::resource('order-request-orders', 'OrderRequestOrderController');
    Route::resource('pickup-delivery-orders', 'PickUpDeliveryOrderController');
    Route::get('product/market_sectors/ajax/{id}', 'ProductController@productMarketSectors');
    Route::get('circle/city/{id}', 'AreaController@cityCircle');
    Route::post('product/approve/{id}', 'ProductController@productApproved')->name('product.approve');
    Route::post('product/flash-sale-approve/{id}', 'ProductController@productFlashSaleApproved')->name('product.flash-sale-approve');
    Route::get('sector-markets/ajax/{id}', 'ProductController@sector_markets');
    Route::resource('driver-payout-requests', 'DriverPayoutRequestController');
    Route::resource('package-order-details', 'PackageOrderDetailController');
    Route::resource('cities', 'CityController');
    Route::resource('areas', 'AreaController');
    Route::resource('vendor-locations', 'VendorLocationController');
    Route::resource('driver-locations', 'DriverLocationController');
    Route::get('dashboard/earning_graph', 'DashboardController@byMonth')->name('dashboard.payment');
    Route::get('update-orders-sector-id', 'OrderController@updateSectorIds');
    Route::post('assign-driver-to-sloted-orders', 'OrderController@assignDriverToOrder');
    Route::post('assign-driver-to-express-orders', 'ExpressOrderController@assignDriver');
    Route::post('assign-driver-to-home-bakers-orders', 'HomeBakersOrderController@assignDriver');
    Route::post('assign-driver-to-pickup-orders', 'PickUpDeliveryOrderController@assignDriver');
    Route::post('assign-driver-to-package-orders', 'PackageOrderController@assignDriver');
    Route::post('assign-driver-to-todays-package-orders', 'TodayPackageOrderController@assignDriver');
    Route::post('assign-driver-to-order-request-orders', 'OrderRequestOrderController@assignDriver');
    Route::post('assign-driver-to-orders', 'OrderController@assignDriver');
    Route::post('add-to-featured-products', 'ProductController@addToFeatured');
    Route::post('remove-from-featured-products', 'ProductController@removeFromFeatured');
    Route::resource('todays-package-orders', 'TodayPackageOrderController');
    Route::resource('return-requests', 'ReturnRequestsController');
    Route::resource('product-approvals', 'ProductApprovalController');
    Route::resource('flash-sales', 'FlashSaleController');
    Route::get('flash-sales/{productId}/approve', 'FlashSaleController@approveFlashSale');
    Route::get('flash-sales/{productId}/delete', 'FlashSaleController@removeFlashSale');
});

Route::get('privacy-policy', 'PrivacyPolicyController@index');

