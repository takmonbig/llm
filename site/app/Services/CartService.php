<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use App\Core\Session;

final class CartService
{
    public const MAX_QTY = 10;

    private static function token(): string
    {
        $t = Session::get('cart_token');
        if (!$t) {
            $t = bin2hex(random_bytes(32));
            Session::set('cart_token', $t);
        }
        return $t;
    }

    /** Returns the cart row, creating it if needed. Associates with the logged-in user. */
    public static function cart(bool $create = true): ?array
    {
        $user = current_user();
        $token = self::token();
        $cart = DB::one('SELECT * FROM carts WHERE token = ?', [$token]);
        if (!$cart && $user) {
            $cart = DB::one('SELECT * FROM carts WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1', [$user['id']]);
            if ($cart) {
                DB::update('carts', ['token' => $token], 'id = ?', [$cart['id']]);
            }
        }
        if (!$cart && $create) {
            $id = DB::insert('carts', ['token' => $token, 'user_id' => $user['id'] ?? null]);
            $cart = DB::one('SELECT * FROM carts WHERE id = ?', [$id]);
        }
        if ($cart && $user && (int) ($cart['user_id'] ?? 0) !== (int) $user['id']) {
            DB::update('carts', ['user_id' => $user['id']], 'id = ?', [$cart['id']]);
        }
        return $cart;
    }

    public static function items(): array
    {
        $cart = self::cart(false);
        if (!$cart) {
            return [];
        }
        return DB::all('SELECT ci.id, ci.quantity, p.id AS product_id, p.title, p.slug, p.price, p.stock, p.is_active,
                (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order LIMIT 1) AS image
            FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ? ORDER BY ci.id', [$cart['id']]);
    }

    public static function add(int $productId, int $qty): array
    {
        $qty = max(1, min(self::MAX_QTY, $qty));
        $product = DB::one('SELECT id, stock FROM products WHERE id = ? AND is_active = 1', [$productId]);
        if (!$product) {
            throw new \InvalidArgumentException('This product is not available.');
        }
        $cart = self::cart();
        $existing = DB::one('SELECT quantity FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cart['id'], $productId]);
        $newQty = min(self::MAX_QTY, (int) ($existing['quantity'] ?? 0) + $qty);
        if ($newQty > (int) $product['stock']) {
            throw new \InvalidArgumentException($product['stock'] > 0 ? 'Only ' . $product['stock'] . ' left in stock.' : 'This product is currently out of stock.');
        }
        DB::run('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)', [$cart['id'], $productId, $newQty]);
        return self::summary();
    }

    public static function setQuantity(int $itemId, int $qty): array
    {
        $cart = self::cart(false);
        if (!$cart) {
            return self::summary();
        }
        if ($qty <= 0) {
            DB::run('DELETE FROM cart_items WHERE id = ? AND cart_id = ?', [$itemId, $cart['id']]);
            return self::summary();
        }
        $qty = min(self::MAX_QTY, $qty);
        $row = DB::one('SELECT ci.product_id, p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.cart_id = ?', [$itemId, $cart['id']]);
        if (!$row) {
            return self::summary();
        }
        if ($qty > (int) $row['stock']) {
            throw new \InvalidArgumentException('Only ' . $row['stock'] . ' left in stock.');
        }
        DB::run('UPDATE cart_items SET quantity = ? WHERE id = ? AND cart_id = ?', [$qty, $itemId, $cart['id']]);
        return self::summary();
    }

    public static function remove(int $itemId): array
    {
        $cart = self::cart(false);
        if ($cart) {
            DB::run('DELETE FROM cart_items WHERE id = ? AND cart_id = ?', [$itemId, $cart['id']]);
        }
        return self::summary();
    }

    public static function clear(): void
    {
        $cart = self::cart(false);
        if ($cart) {
            DB::run('DELETE FROM cart_items WHERE cart_id = ?', [$cart['id']]);
        }
    }

    public static function shippingFee(float $subtotal): float
    {
        if ($subtotal <= 0 || \App\Models\Setting::bool('free_shipping', true)) {
            return 0.0;
        }
        return (float) \App\Models\Setting::get('flat_shipping_fee', 0);
    }

    public static function summary(): array
    {
        $items = self::items();
        $subtotal = 0.0;
        $count = 0;
        foreach ($items as &$it) {
            $it['price'] = (float) $it['price'];
            $it['line_total'] = round($it['price'] * (int) $it['quantity'], 2);
            $it['image_url'] = product_image_url($it['image']);
            $it['url'] = url('product/' . $it['slug']);
            $subtotal += $it['line_total'];
            $count += (int) $it['quantity'];
        }
        unset($it);
        $shipping = self::shippingFee($subtotal);
        return [
            'items' => $items,
            'count' => $count,
            'subtotal' => round($subtotal, 2),
            'shipping' => $shipping,
            'total' => round($subtotal + $shipping, 2),
            'subtotal_fmt' => money($subtotal),
            'shipping_fmt' => $shipping > 0 ? money($shipping) : 'Free',
            'total_fmt' => money($subtotal + $shipping),
        ];
    }
}
