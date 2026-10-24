<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Order;

final class OrderService
{
    public const STATES = ['Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh', 'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry'];

    /** Validate checkout input. Returns [errors, clean]. */
    public static function validate(array $in): array
    {
        $errors = [];
        $clean = [];
        $clean['customer_name'] = trim((string) ($in['full_name'] ?? ''));
        if (mb_strlen($clean['customer_name']) < 2 || mb_strlen($clean['customer_name']) > 120) {
            $errors['full_name'] = 'Please enter your full name.';
        }
        $phone = preg_replace('/\D/', '', (string) ($in['phone'] ?? '')) ?? '';
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $errors['phone'] = 'Enter a valid 10-digit Indian mobile number.';
        }
        $clean['phone'] = $phone;
        $email = strtolower(trim((string) ($in['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        $clean['email'] = $email;
        $clean['ship_line1'] = trim((string) ($in['line1'] ?? ''));
        if (mb_strlen($clean['ship_line1']) < 5) {
            $errors['line1'] = 'Please enter your street address.';
        }
        $clean['ship_line2'] = mb_substr(trim((string) ($in['line2'] ?? '')), 0, 255) ?: null;
        $clean['ship_landmark'] = mb_substr(trim((string) ($in['landmark'] ?? '')), 0, 255) ?: null;
        $clean['ship_city'] = trim((string) ($in['city'] ?? ''));
        if (mb_strlen($clean['ship_city']) < 2) {
            $errors['city'] = 'Please enter your city.';
        }
        $clean['ship_state'] = trim((string) ($in['state'] ?? ''));
        if (!in_array($clean['ship_state'], self::STATES, true)) {
            $errors['state'] = 'Please select your state.';
        }
        $pin = trim((string) ($in['pincode'] ?? ''));
        if (!preg_match('/^[1-9]\d{5}$/', $pin)) {
            $errors['pincode'] = 'Enter a valid 6-digit PIN code.';
        }
        $clean['ship_pincode'] = $pin;
        $clean['ship_country'] = 'India';
        $clean['customer_note'] = mb_substr(trim((string) ($in['note'] ?? '')), 0, 500) ?: null;
        $method = (string) ($in['payment_method'] ?? '');
        $allowed = self::paymentMethods();
        if (!isset($allowed[$method])) {
            $errors['payment_method'] = 'Please choose an available payment method.';
        }
        $clean['payment_method'] = $method;
        return [$errors, $clean];
    }

    public static function paymentMethods(): array
    {
        $m = [];
        if (\App\Models\Setting::bool('razorpay_enabled', true) && RazorpayService::configured()) {
            $m['razorpay'] = 'Pay Online (UPI, Cards, Net Banking, Wallets)';
        }
        if (\App\Models\Setting::bool('cod_enabled', true)) {
            $m['cod'] = 'Cash on Delivery';
        }
        return $m;
    }

    /** Creates the order from the current cart inside a transaction, decrementing stock safely. */
    public static function place(array $clean): array
    {
        $summary = CartService::summary();
        if (!$summary['items']) {
            throw new \RuntimeException('Your cart is empty.');
        }
        $user = current_user();
        $isOnline = $clean['payment_method'] === 'razorpay';

        return DB::tx(function () use ($summary, $clean, $user, $isOnline) {
            $subtotal = 0.0;
            $lines = [];
            foreach ($summary['items'] as $item) {
                $p = DB::one('SELECT id, title, sku, price, stock, is_active FROM products WHERE id = ? FOR UPDATE', [$item['product_id']]);
                if (!$p || !$p['is_active']) {
                    throw new \RuntimeException($item['title'] . ' is no longer available.');
                }
                $qty = (int) $item['quantity'];
                $updated = DB::run('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?', [$qty, $p['id'], $qty])->rowCount();
                if ($updated !== 1) {
                    throw new \RuntimeException('Sorry, only ' . $p['stock'] . ' unit(s) of ' . $p['title'] . ' left in stock.');
                }
                $lineTotal = round((float) $p['price'] * $qty, 2);
                $subtotal += $lineTotal;
                $lines[] = ['product_id' => $p['id'], 'product_title' => $p['title'], 'product_sku' => $p['sku'], 'product_image' => $item['image'], 'unit_price' => $p['price'], 'quantity' => $qty, 'line_total' => $lineTotal];
            }
            $shipping = CartService::shippingFee($subtotal);
            $total = round($subtotal + $shipping, 2);
            $orderId = DB::insert('orders', array_merge($clean, [
                'order_number' => Order::generateNumber(),
                'user_id' => $user['id'] ?? null,
                'access_token' => bin2hex(random_bytes(32)),
                'subtotal' => $subtotal,
                'shipping_fee' => $shipping,
                'total' => $total,
                'payment_status' => 'pending',
                'status' => $isOnline ? 'pending_payment' : 'placed',
                'ip_address' => Request::ip(),
            ]));
            foreach ($lines as $line) {
                DB::insert('order_items', $line + ['order_id' => $orderId]);
            }
            DB::insert('order_status_history', ['order_id' => $orderId, 'from_status' => null, 'to_status' => $isOnline ? 'pending_payment' : 'placed', 'note' => $isOnline ? 'Awaiting online payment' : 'Cash on Delivery order placed', 'changed_by' => 'customer']);
            $order = Order::find($orderId);
            Session::rememberOrder($orderId);
            return $order;
        });
    }

    public static function afterPlaced(array $order): void
    {
        CartService::clear();
        self::sendConfirmation($order);
    }

    public static function sendConfirmation(array $order): void
    {
        $items = Order::items((int) $order['id']);
        $html = View::render('emails/order-confirmation', ['order' => $order, 'items' => $items], null);
        Mailer::send($order['email'], 'Order ' . $order['order_number'] . ' confirmed — ' . setting('store_name', 'Long Live Men'), $html);
    }

    public static function sendTrackingLink(array $order): void
    {
        $token = Order::createAccessToken((int) $order['id']);
        $link = url('track/' . $order['order_number'] . '/' . $token);
        $html = View::render('emails/tracking-link', ['order' => $order, 'link' => $link], null);
        Mailer::send($order['email'], 'Track your order ' . $order['order_number'], $html);
    }
}
