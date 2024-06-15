<?php
// src/Service/StripeService.php


namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct(string $stripeSecretKey)
    {
        Stripe::setApiKey($stripeSecretKey);
    }

    public function createPaymentIntent(float $amount, string $currency): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount * 100, // Stripe requires the amount in cents
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);
    }
}
