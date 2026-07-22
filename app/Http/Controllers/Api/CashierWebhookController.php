<?php

namespace App\Http\Controllers\Api;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookHandler;

/**
 * Thin wrapper around Cashier's webhook handler.
 *
 * No longer a standalone route endpoint — instantiated internally
 * by StripeWebhookController to process subscription events.
 *
 * Extending Cashier's WebhookController allows us to override
 * specific handlers (e.g. handleInvoicePaymentSucceeded) in the future
 * if custom logic is needed alongside Cashier's default behavior.
 */
class CashierWebhookController extends CashierWebhookHandler
{
    //
}
