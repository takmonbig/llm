<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Order
{
    public const STATUSES = ['pending_payment', 'placed', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

    public const TRANSITIONS = [
        'pending_payment' => ['placed', 'cancelled'],
        'placed' => ['confirmed', 'processing', 'cancelled'],
        'confirmed' => ['processing', 'shipped', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'returned'],
        'delivered' => ['returned'],
        'cancelled' => [],
        'returned' => [],
    ];

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    public static function findByNumber(string $number): ?array
    {
        return DB::one('SELECT * FROM orders WHERE order_number = ?', [$number]);
    }

    public static function items(int $orderId): array
    {
        return DB::all('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    public static function history(int $orderId): array
    {
        return DB::all('SELECT * FROM order_status_history WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    public static function payments(int $orderId): array
    {
        return DB::all('SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC', [$orderId]);
    }

    public static function forUser(int $userId): array
    {
        return DB::all('SELECT o.*, (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count FROM orders o WHERE user_id = ? ORDER BY id DESC', [$userId]);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'LLM-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
        } while (DB::value('SELECT COUNT(*) FROM orders WHERE order_number = ?', [$number]) > 0);
        return $number;
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    /** Change status with history and stock restoration on cancellation. Caller handles transaction if needed. */
    public static function setStatus(int $orderId, string $to, string $by = 'system', ?string $note = null): bool
    {
        $order = self::find($orderId);
        if (!$order || !self::canTransition($order['status'], $to)) {
            return false;
        }
        DB::update('orders', ['status' => $to], 'id = ?', [$orderId]);
        DB::insert('order_status_history', ['order_id' => $orderId, 'from_status' => $order['status'], 'to_status' => $to, 'note' => $note, 'changed_by' => $by]);
        if (in_array($to, ['cancelled', 'returned'], true)) {
            foreach (self::items($orderId) as $item) {
                if ($item['product_id']) {
                    DB::run('UPDATE products SET stock = stock + ? WHERE id = ?', [(int) $item['quantity'], (int) $item['product_id']]);
                }
            }
        }
        return true;
    }

    public static function createAccessToken(int $orderId): string
    {
        DB::run('DELETE FROM order_access_tokens WHERE expires_at < NOW()');
        $token = bin2hex(random_bytes(24));
        DB::insert('order_access_tokens', ['order_id' => $orderId, 'token_hash' => hash('sha256', $token), 'expires_at' => date('Y-m-d H:i:s', time() + 86400 * 2)]);
        return $token;
    }

    public static function findByAccessToken(string $number, string $token): ?array
    {
        $order = self::findByNumber($number);
        if (!$order) {
            return null;
        }
        if (hash_equals($order['access_token'], $token)) {
            return $order;
        }
        $row = DB::one('SELECT id FROM order_access_tokens WHERE order_id = ? AND token_hash = ? AND expires_at > NOW()', [$order['id'], hash('sha256', $token)]);
        return $row ? $order : null;
    }
}
