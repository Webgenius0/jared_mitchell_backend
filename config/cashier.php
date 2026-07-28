<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe Keys
    |--------------------------------------------------------------------------
    |
    | Stripe publishable key and secret key.
    | These are usually defined in config/stripe.php, but Cashier also
    | falls back to STRIPE_KEY / STRIPE_SECRET environment variables.
    |
    */

    'key' => env('STRIPE_KEY'),

    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Cashier Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI path where Cashier's views, such as the payment
    | method form, will be available from. You're free to tweak this path
    | to match your application's needs.
    |
    */

    'path' => env('CASHIER_PATH', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Cashier Webhook
    |--------------------------------------------------------------------------
    |
    | Cashier's webhook handler URL is configured here. You may configure
    | a custom webhook path for Cashier's built-in webhook handling.
    |
    */

    'webhook' => [
        'path' => env('CASHIER_WEBHOOK_PATH', 'webhooks/stripe/cashier'),
        'tries' => env('CASHIER_WEBHOOK_TRIES', 3),
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used when generating charges
    | from your application. Feel free to customize this to any of the
    | currencies supported by Stripe.
    |
    */

    'currency' => env('CASHIER_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale in which your money values are formatted in
    | for display. You may use any of the locales supported by Stripe or
    | the underlying MoneyPHP library.
    |
    */

    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Payment Confirmation Notification
    |--------------------------------------------------------------------------
    |
    | If this setting is enabled, Cashier will automatically notify your
    | customer via email when a payment confirmation is required. You
    | can customize the notification class used here.
    |
    */

    'payment_notification' => \Laravel\Cashier\Notifications\ConfirmPayment::class,

    /*
    |--------------------------------------------------------------------------
    | Invoice Paper Size
    |--------------------------------------------------------------------------
    |
    | This option is for the generation of invoice PDFs. You may specify the
    | paper size for your invoices to match the one used by your application.
    |
    */

    'invoice' => [
        'paper' => [
            'size' => 'letter',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Date Format
    |--------------------------------------------------------------------------
    |
    | This option controls the format of the date shown on generated PDF
    | invoices. You may use any of the format options supported by
    | Carbon when setting this value.
    |
    */

    'invoice_date_format' => env('CASHIER_INVOICE_DATE_FORMAT', 'Y-m-d'),

];
