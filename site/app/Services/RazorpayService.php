<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use App\Core\Logger;
use App\Models\Order;

/** Razorpay Orders API client (curl) + signature verification + idempotent payment handling. */
final class RazorpayService
{
    private const API = 'https://api.razorpay.com/v1';

    public static function configured(): bool
    {
        return (string) env('RAZORPAY_KEY_ID', '') !== '' && (string) env('RAZORPAY_KEY_SECRET', '') !== '';
    }

    public static function keyId(): string
    {
        return (string) env('RAZORPAY_KEY_ID', '');
    }

    public static function mode(): string
    {
        $mode = strtolower((string) env('RAZORPAY_MODE', 'test'));
        return $mode === 'live' && str_starts_with(self::keyId(), 'rzp_live_') ? 'live' : 'test';
    }

    private static function request(string $method, string $path, ?array $body = null): array
    {
        $ch = curl_init(self::API . $path);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => self::keyId() . ':' . env('RAZORPAY_KEY_SECRET'),
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        ];
        if ($body !== null) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($res === false) {
            throw new \RuntimeException('Razorpay connection failed: ' . $err);
        }
        $data = json_decode((string) $res, true) ?: [];
        if ($status >= 400) {
            $msg = $data['error']['description'] ?? 'Razorpay request failed';
            Logger::error('Razorpay API error', ['path' => $path, 'status' => $status, 'body' => $data]);
            throw new \RuntimeException($msg);
        }
        return $data;
    }

    /** Create (or reuse) a Razorpay order for our order. Amount in paise. */
    public static function createOrder(array $order): array
    {
        $existing = DB::one('SELECT * FROM payments WHERE order_id = ? AND provider = "razorpay" AND status = "created" AND provider_order_id IS NOT NULL ORDER BY id DESC LIMIT 1', [$order['id']]);
        if ($existing) {
            return ['id' => $existing['provider_order_id'], 'amount' => (int) round((float) $existing['amount'] * 100), 'currency' => $existing['currency']];
        }
        $amount = (int) round((float) $order['total'] * 100);
        $rz = self::request('POST', '/orders', [
            'amount' => $amount,
            'currency' => 'INR',
            'receipt' => substr($order['order_number'], 0, 40),
            'notes' => ['order_number' => $order['order_number']],
        ]);
        DB::insert('payments', [
            'order_id' => $order['id'],
            'provider' => 'razorpay',
            'mode' => self::mode(),
            'provider_order_id' => $rz['id'],
            'amount' => $order['total'],
            'currency' => 'INR',
            'status' => 'created',
            'raw_response' => json_encode($rz),
        ]);
        return $rz;
    }

    public static function verifyCheckoutSignature(string $rzOrderId, string $rzPaymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $rzOrderId . '|' . $rzPaymentId, (string) env('RAZORPAY_KEY_SECRET'));
        return hash_equals($expected, $signature);
    }

    public static function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = (string) env('RAZORPAY_WEBHOOK_SECRET', '');
        if ($secret === '' || $signature === '') {
            return false;
        }
        return hash_equals(hash_hmac('sha256', $rawBody, $secret), $signature);
    }

    public static function fetchPayment(string $paymentId): array
    {
        return self::request('GET', '/payments/' . rawurlencode($paymentId));
    }

    /**
     * Mark an order paid from server-verified payment data. Idempotent: duplicate calls are no-ops.
     * $payment is the Razorpay payment entity (status must be captured, or authorized when auto-capture is pending).
     */
    public static function recordSuccessfulPayment(array $payment, string $source): ?array
    {
        $rzOrderId = $payment['order_id'] ?? '';
        $paymentId = $payment['id'] ?? '';
        if ($rzOrderId === '' || $paymentId === '') {
            return null;
        }
        return DB::tx(function () use ($payment, $rzOrderId, $paymentId, $source) {
            $pay = DB::one('SELECT p.*, o.total AS order_total, o.id AS oid FROM payments p JOIN orders o ON o.id = p.order_id WHERE p.provider_order_id = ? ORDER BY p.id DESC LIMIT 1 FOR UPDATE', [$rzOrderId]);
            if (!$pay) {
                Logger::error('Payment for unknown Razorpay order', ['rz_order' => $rzOrderId]);
                return null;
            }
            $order = DB::one('SELECT * FROM orders WHERE id = ? FOR UPDATE', [$pay['oid']]);
            $expectedPaise = (int) round((float) $order['total'] * 100);
            if ((int) ($payment['amount'] ?? 0) !== $expectedPaise || ($payment['currency'] ?? 'INR') !== 'INR') {
                Logger::error('Razorpay amount mismatch', ['order' => $order['order_number'], 'expected' => $expectedPaise, 'got' => $payment['amount'] ?? null]);
                return null;
            }
            $status = $payment['status'] ?? '';
            if (!in_array($status, ['captured', 'authorized'], true)) {
                return null;
            }
            $already = DB::one('SELECT id FROM payments WHERE provider = "razorpay" AND provider_payment_id = ?', [$paymentId]);
            if ($already) {
                DB::update('payments', ['status' => $status === 'captured' ? 'captured' : 'authorized', 'signature_verified' => 1], 'id = ?', [$already['id']]);
            } else {
                DB::update('payments', [
                    'provider_payment_id' => $paymentId,
                    'status' => $status === 'captured' ? 'captured' : 'authorized',
                    'method' => $payment['method'] ?? null,
                    'signature_verified' => 1,
                    'raw_response' => json_encode($payment),
                ], 'id = ?', [$pay['id']]);
            }
            if ($order['payment_status'] !== 'paid') {
                DB::update('orders', ['payment_status' => 'paid'], 'id = ?', [$order['id']]);
                if ($order['status'] === 'pending_payment') {
                    Order::setStatus((int) $order['id'], 'placed', $source, 'Payment ' . $paymentId . ' verified (' . $status . ')');
                }
                $order = Order::find((int) $order['id']);
                $order['_just_paid'] = true;
            }
            return $order;
        });
    }

    public static function recordFailedPayment(string $rzOrderId, ?string $paymentId, ?string $code, ?string $description): void
    {
        $pay = DB::one('SELECT id, order_id FROM payments WHERE provider_order_id = ? ORDER BY id DESC LIMIT 1', [$rzOrderId]);
        if (!$pay) {
            return;
        }
        DB::update('payments', ['status' => 'failed', 'provider_payment_id' => $paymentId ?: null, 'error_code' => $code, 'error_description' => $description ? mb_substr($description, 0, 255) : null], 'id = ? AND status = "created"', [$pay['id']]);
        $order = Order::find((int) $pay['order_id']);
        if ($order && $order['payment_status'] === 'pending') {
            DB::update('orders', ['payment_status' => 'failed'], 'id = ?', [$order['id']]);
            DB::insert('order_status_history', ['order_id' => $order['id'], 'from_status' => $order['status'], 'to_status' => $order['status'], 'note' => 'Payment failed: ' . ($description ?: $code ?: 'unknown'), 'changed_by' => 'razorpay']);
        }
    }

    /** Process a webhook payload idempotently. Returns true if accepted. */
    public static function handleWebhook(array $event, string $eventId): bool
    {
        $type = (string) ($event['event'] ?? '');
        $payment = $event['payload']['payment']['entity'] ?? [];
        try {
            DB::insert('payment_events', [
                'provider' => 'razorpay',
                'event_id' => $eventId,
                'event_type' => $type,
                'provider_order_id' => $payment['order_id'] ?? null,
                'provider_payment_id' => $payment['id'] ?? null,
                'payload' => json_encode($event),
            ]);
        } catch (\PDOException $e) {
            Logger::info('Duplicate webhook ignored', ['event_id' => $eventId]);
            return true;
        }
        if (in_array($type, ['payment.captured', 'payment.authorized', 'order.paid'], true) && $payment) {
            $order = self::recordSuccessfulPayment($payment, 'webhook');
            if ($order && !empty($order['_just_paid'])) {
                OrderService::sendConfirmation($order);
            }
        } elseif ($type === 'payment.failed' && $payment) {
            self::recordFailedPayment((string) ($payment['order_id'] ?? ''), $payment['id'] ?? null, $payment['error_code'] ?? null, $payment['error_description'] ?? null);
        }
        DB::run('UPDATE payment_events SET processed_at = NOW() WHERE provider = "razorpay" AND event_id = ?', [$eventId]);
        return true;
    }
}
