<?php

require_once APP_PATH . '/models/Subscription.php';
require_once APP_PATH . '/models/Usuario.php';
require_once APP_PATH . '/services/StripeService.php';
require_once APP_PATH . '/services/NotificationService.php';

class SubscriptionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('subscription/index', [
            'active' => 'subscription',
            'isPremium' => Usuario::isPremium((int) current_user()['id']),
            'title' => 'Premium - FastPlay',
        ]);
    }

    public function checkout(): void
    {
        $this->requireAuth();
        $this->requirePost();
        $session = (new StripeService())->createCheckoutSession((int) current_user()['id']);
        redirect($session['checkout_url']);
    }

    public function success(): void
    {
        $this->requireAuth();
        $userId = (int) current_user()['id'];
        
        $sessionId = $_GET['session_id'] ?? '';
        if (!$sessionId && empty($_GET['demo'])) {
            flash('warn', 'Sesión de pago no válida.');
            redirect('subscription');
        }

        (new Subscription())->upsertLocal($userId, 'active');
        Database::run('UPDATE users SET is_premium = 1 WHERE id = ?', [$userId]);
        
        NotificationService::create($userId, 'subscription_activated', 'Tu suscripción premium está activa.', 'subscription');
        flash('ok', 'Suscripción premium activada.');
        redirect('teams/create');
    }

    public function cancel(): void
    {
        $this->requireAuth();
        flash('warn', 'El proceso de pago se canceló.');
        redirect('subscription');
    }
}
