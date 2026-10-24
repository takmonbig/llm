<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Order;
use App\Services\OrderService;

final class OrderController
{
    public function confirmation(string $number): void
    {
        $order = Order::findByNumber($number);
        $user = current_user();
        if (!$order || !(Session::ownsOrder((int) $order['id']) || ($user && (int) $order['user_id'] === (int) $user['id']))) {
            abort(404);
        }
        View::show('order-confirmation', [
            'order' => $order,
            'items' => Order::items((int) $order['id']),
            'meta' => ['title' => 'Order ' . $order['order_number'] . ' | ' . setting('store_name'), 'robots' => 'noindex'],
        ]);
    }

    public function trackForm(): void
    {
        View::show('track', ['meta' => ['title' => 'Track Your Order | ' . setting('store_name'), 'canonical' => url('track')]]);
    }

    public function trackRequest(): void
    {
        if (!RateLimiter::hit('track:' . Request::ip(), 10, 600)) {
            Session::flash('error', 'Too many attempts. Please try again later.');
            redirect('/track');
        }
        $number = strtoupper(trim((string) Request::input('order_number', '')));
        $email = strtolower(trim((string) Request::input('email', '')));
        $order = Order::findByNumber($number);
        $user = current_user();
        if ($order && $user && (int) $order['user_id'] === (int) $user['id']) {
            redirect('/account/orders/' . $order['order_number']);
        }
        if ($order && hash_equals($order['email'], $email)) {
            OrderService::sendTrackingLink($order);
        }
        Session::flash('success', 'If the order number and email match, a secure tracking link has been sent to your email. It is valid for 48 hours.');
        redirect('/track');
    }

    public function trackView(string $number, string $token): void
    {
        $order = Order::findByAccessToken(strtoupper($number), $token);
        if (!$order) {
            abort(404, 'This tracking link is invalid or has expired.');
        }
        View::show('order-detail', [
            'order' => $order,
            'items' => Order::items((int) $order['id']),
            'history' => Order::history((int) $order['id']),
            'meta' => ['title' => 'Order ' . $order['order_number'] . ' | ' . setting('store_name'), 'robots' => 'noindex'],
            'guest' => true,
        ]);
    }
}
