<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'perfectcorp' => [
        'api_key' => env('PERFECT_CORP_API_KEY'),
        'base_url' => 'https://yce-api-01.makeupar.com',
        'enabled' => env('SKIN_ANALYSIS_AI_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'infobip'),
        'infobip' => [
            'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
            'api_key'  => env('INFOBIP_API_KEY', ''),
            'sender'   => env('INFOBIP_SENDER', 'GEL Cabinet'),
        ],
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
            'auth_token'  => env('TWILIO_AUTH_TOKEN', ''),
            'from'        => env('TWILIO_FROM', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Configuration
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'infobip'),
        'infobip' => [
            'base_url' => env('WHATSAPP_INFOBIP_URL', env('INFOBIP_BASE_URL', 'https://api.infobip.com')),
            'api_key'  => env('WHATSAPP_INFOBIP_KEY', env('INFOBIP_API_KEY', '')),
            'sender'   => env('WHATSAPP_SENDER', 'GEL Cabinet'),
            'namespace' => env('WHATSAPP_NAMESPACE', ''),
        ],
        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
            'auth_token'  => env('TWILIO_AUTH_TOKEN', ''),
            'from'        => env('WHATSAPP_TWILIO_FROM', 'whatsapp:+14155238886'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile Money (Bénin)
    |--------------------------------------------------------------------------
    */
    'mobile_money' => [
        'default' => env('MOMO_PROVIDER', 'mtn'),
        'mtn' => [
            'api_key'             => env('MTN_MOMO_API_KEY', ''),
            'api_user'            => env('MTN_MOMO_API_USER', ''),
            'subscription_key'    => env('MTN_MOMO_SUBSCRIPTION_KEY', ''),
            'environment'         => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
            'collection_primary_key'  => env('MTN_MOMO_COLLECTION_PRIMARY_KEY', ''),
            'disbursement_primary_key' => env('MTN_MOMO_DISBURSEMENT_PRIMARY_KEY', ''),
            'base_url'            => env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
            'currency'            => 'EUR',
        ],
        'moov' => [
            'api_key'     => env('MOOV_MOMO_API_KEY', ''),
            'api_secret'  => env('MOOV_MOMO_API_SECRET', ''),
            'merchant_id' => env('MOOV_MOMO_MERCHANT_ID', ''),
            'base_url'    => env('MOOV_MOMO_BASE_URL', 'https://api.moov-africa.com'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | OCR Configuration
    |--------------------------------------------------------------------------
    */
    'ocr' => [
        'engine' => env('OCR_ENGINE', 'tesseract'),
        'tesseract_path' => env('TESSERACT_PATH', 'tesseract'),
        'google_credentials' => env('GOOGLE_VISION_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI & Machine Learning (Gemini)
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'api_key' => env('AI_API_KEY', ''),
        'api_url' => env('AI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent'),
        'model' => env('AI_MODEL', 'gemini-1.5-pro'),
    ],

];
