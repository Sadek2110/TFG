<?php

class StripeService
{
    public function __construct()
    {
        \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY') ?: 'sk_test_123');
    }

    public function createCheckoutSession(int $userId): array
    {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => ['name' => 'Suscripción FastPlay Premium'],
                    'unit_amount' => 500, // 5.00 €
                    'recurring' => ['interval' => 'month'],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => url('subscription/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('subscription/cancel'),
            'client_reference_id' => (string) $userId,
        ]);
        return [
            'mode' => 'subscription',
            'provider' => 'stripe',
            'checkout_url' => $session->url,
        ];
    }

    public function retrieveSubscription(string $id): array
    {
        return ['id' => $id, 'status' => 'active'];
    }

    public function cancelSubscription(string $id): array
    {
        return ['id' => $id, 'status' => 'cancelled'];
    }
}
