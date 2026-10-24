<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\Request;
use App\Core\Session;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\RazorpayService;

final class PaymentController
{
    /** Frontend callback after Checkout.js success: verify signature + fetch payment server-side. */
    public function verify(): void
    {
        $rzOrderId = (string) Request::input('razorpay_order_id', '');
        $paymentId = (string) Request::input('razorpay_payment_id', '');
        $signature = (string) Request::input('razorpay_signature', '');
        if ($rzOrderId === '' || $paymentId === '' || $signature === '' || !RazorpayService::verifyCheckoutSignature($rzOrderId, $paymentId, $signature)) {
            Logger::error('Razorpay signature verification failed', ['rz_order' => $rzOrderId, 'payment' => $paymentId]);
            json_response(['ok' => false, 'error' => 'Payment verification failed. If money was deducted it will be reconciled automatically.'], 400);
        }
        try {
            $payment = RazorpayService::fetchPayment($paymentId);
        } catch (\Throwable $e) {
            json_response(['ok' => false, 'error' => 'Could not confirm payment with Razorpay. Please contact support with your order number.'], 502);
        }
        if (($payment['order_id'] ?? '') !== $rzOrderId) {
            json_response(['ok' => false, 'error' => 'Payment does not match this order.'], 400);
        }
        $order = RazorpayService::recordSuccessfulPayment($payment, 'customer');
        if (!$order) {
            json_response(['ok' => false, 'error' => 'Payment could not be confirmed. Our team will reconcile it shortly.'], 409);
        }
        if (!empty($order['_just_paid'])) {
            OrderService::afterPlaced($order);
        }
        Session::rememberOrder((int) $order['id']);
        json_response(['ok' => true, 'redirect' => url('order/confirmation/' . $order['order_number'])]);
    }

    public function failed(): void
    {
        $rzOrderId = (string) Request::input('razorpay_order_id', '');
        if ($rzOrderId !== '') {
            RazorpayService::recordFailedPayment($rzOrderId, Request::input('razorpay_payment_id'), Request::input('code'), Request::input('description'));
        }
        $number = (string) Request::input('order_number', '');
        json_response(['ok' => true, 'retry_url' => $number ? url('payment/retry/' . $number) : url('checkout')]);
    }

    public function retry(string $number): void
    {
        $order = Order::findByNumber($number);
        if (!$order || !$this->canAccess($order)) {
            abort(404);
        }
        if ($order['payment_status'] === 'paid' || $order['status'] !== 'pending_payment') {
            redirect('/order/confirmation/' . $order['order_number']);
        }
        if (!RazorpayService::configured()) {
            Session::flash('error', 'Online payment is currently unavailable.');
            redirect('/order/confirmation/' . $order['order_number']);
        }
        $rz = RazorpayService::createOrder($order);
        \App\Core\View::show('payment-retry', [
            'order' => $order,
            'rz' => [
                'key' => RazorpayService::keyId(),
                'order_id' => $rz['id'],
                'amount' => $rz['amount'],
                'currency' => 'INR',
                'name' => setting('store_name', 'Long Live Men'),
                'description' => 'Order ' . $order['order_number'],
                'prefill' => ['name' => $order['customer_name'], 'email' => $order['email'], 'contact' => $order['phone']],
            ],
            'meta' => ['title' => 'Complete payment | ' . setting('store_name'), 'robots' => 'noindex'],
        ]);
    }

    /** Razorpay webhook: validates signature, processes idempotently. */
    public function webhook(): void
    {
        $raw = Request::rawBody();
        $sig = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
        if (!RazorpayService::verifyWebhookSignature($raw, $sig)) {
            Logger::error('Webhook signature invalid');
            json_response(['ok' => false, 'error' => 'invalid signature'], 400);
        }
        $event = json_decode($raw, true);
        if (!is_array($event) || empty($event['event'])) {
            json_response(['ok' => false, 'error' => 'bad payload'], 400);
        }
        $eventId = $_SERVER['HTTP_X_RAZORPAY_EVENT_ID'] ?? ('sha-' . hash('sha256', $raw));
        RazorpayService::handleWebhook($event, (string) $eventId);
        json_response(['ok' => true]);
    }

    private function canAccess(array $order): bool
    {
        $user = current_user();
        return Session::ownsOrder((int) $order['id']) || ($user && (int) $order['user_id'] === (int) $user['id']);
    }
}
