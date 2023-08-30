<?php


/**
 * File name: services.php
 * Last modified: 2020.06.11 at 16:03:23
 * Author: Pixbit Solutions - https://pixbitsolutions.com
 * Copyright (c) 2020
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
    ],

    'facebook' => [
        'client_id' => '2088483311467392',         // Your Facebook Client ID
        'client_secret' => '4fb7bff52d8eb13041f6fc59030e4b62', // Your Facebook Client Secret
        'redirect' => 'https://owleto.com/public/login/facebook/callback',
    ],

    'google' => [
        'client_id' => '527129559488-roolg8aq110p8r1q952fqa9tm06gbloe.apps.googleusercontent.com',         // Your google Client ID
        'client_secret' => 'FpIi8SLgc69ZWodk-xHaOrxn', // Your google Client Secret
        'redirect' => 'https://owleto.com/public/login/google/callback',
        'map_api_key' => env('GOOGLE_MAP_API_KEY')
    ],


    'twitter' => [
        'client_id' => '',         // Your twitter Client ID
        'client_secret' => '', // Your twitter Client Secret
        'redirect' => 'https://owleto.com/public/login/twitter/callback',
    ],

    'braintree' => [
        'model'  => App\Models\User::class,
        'environment' => env('BRAINTREE_ENV'),
        'merchant_id' => env('BRAINTREE_MERCHANT_ID'),
        'public_key' => env('BRAINTREE_PUBLIC_KEY'),
        'private_key' => env('BRAINTREE_PRIVATE_KEY'),
    ],

    'razorpay' => [
        'api_key' => env('RAZORPAY_API_KEY'),
        'api_secret' => env('RAZORPAY_API_SECRET'),
    ],

    'fcm' => [
        'key' => '',
    ],

    'instantalerts' => [
            'apikey' => env('INSTANTALERT_APIKEY'),
            'sender' => env('INSTANTALERT_SENDER'),
        ],

    'twilio' => [
        'twilio_sid' => env('TWILIO_SID'),
        'twilio_token' => env('TWILIO_TOKEN'),
        'twilio_msg_service_id' => env('TWILIO_MSG_SERVICE_ID'),
    ],

    'driver-assign-time' => [
        'driver_assign_morning_start' => env('DRIVER_ASSIGN_MNG_TIME_START'),
        'driver_assign_morning_end' => env('DRIVER_ASSIGN_MNG_TIME_END'),
        'driver_assign_evening_start' => env('DRIVER_ASSIGN_ENG_TIME_START'),
        'driver_assign_evening_end' => env('DRIVER_ASSIGN_ENG_TIME_END'),
    ],

    'delivery-fee' => [
        'express' => env('EXPRESS_DELIVERY_FEE_PER_KM'),
        'custom' => env('CUSTOM_DELIVERY_FEE_PER_KM'),
        'slot' => env('SLOTED_DELIVERY_FEE_PER_KM'),
    ]

];
