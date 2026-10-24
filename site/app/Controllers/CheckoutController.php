<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Logger;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\RazorpayService;

final class CheckoutController
{
    public function show(): void
    {
        $cart = CartService::summary();
        if (!$cart['items']) {
            Session::flash('error', 'Your cart is empty. Add the combo to continue.');
            redirect('/cart');
        }
        $user = current_user();
        $address = $user ? (User::addresses((int) $user['id'])[0] ?? null) : null;
        View::show('checkout', [
            'cart' => $cart,
            'user' => $user,
            'address' => $address,
            'methods' => OrderService::paymentMethods(),
            'states' => OrderService::STATES,
            'errors' => Session::get('_errors', []),
            'razorpayKey' => RazorpayService::keyId(),
            'meta' => ['title' => 'Secure Checkout | ' . setting('store_name'), 'canonical' => url('checkout'), 'robots' => 'noindex'],
            'bodyClass' => 'page-checkout',
        ]);
        Session::forget('_errors');
    }

    public function place(): void
    {
        if (!RateLimiter::hit('checkout:' . Request::ip(), 15, 600)) {
            $this->fail(['_form' => 'Too many attempts. Please wait a few minutes and try again.']);
        }
        [$errors, $clean] = OrderService::validate(Request::all());
        if ($errors) {
            $this->fail($errors);
        }
        try {
            $order = OrderService::place($clean);
        } catch (\RuntimeException $e) {
            $this->fail(['_form' => $e->getMessage()]);
        }
        if ($clean['payment_method'] === 'razorpay') {
            try {
                $rz = RazorpayService::createOrder($order);
            } catch (\Throwable $e) {
                Logger::error('Razorpay order creation failed', ['order' => $order['order_number'], 'err' => $e->getMessage()]);
                $this->fail(['_form' => 'We could not start the online payment. Please try again or choose Cash on Delivery.']);
            }
            json_response([
                'ok' => true,
                'payment' => 'razorpay',
                'order_number' => $order['order_number'],
                'razorpay' => [
                    'key' => RazorpayService::keyId(),
                    'order_id' => $rz['id'],
                    'amount' => $rz['amount'],
                    'currency' => 'INR',
                    'name' => setting('store_name', 'Long Live Men'),
                    'description' => 'Order ' . $order['order_number'],
                    'prefill' => ['name' => $order['customer_name'], 'email' => $order['email'], 'contact' => $order['phone']],
                    'notes' => ['order_number' => $order['order_number']],
                ],
                'failed_url' => url('payment/razorpay/failed'),
                'verify_url' => url('payment/razorpay/verify'),
            ]);
        }
        OrderService::afterPlaced($order);
        $redirect = url('order/confirmation/' . $order['order_number']);
        if (Request::wantsJson()) {
            json_response(['ok' => true, 'payment' => 'cod', 'redirect' => $redirect]);
        }
        redirect($redirect);
    }

    private function fail(array $errors): never
    {
        if (Request::wantsJson()) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }
        Session::set('_errors', $errors);
        Session::set('_old', $_POST);
        redirect('/checkout');
    }
}
