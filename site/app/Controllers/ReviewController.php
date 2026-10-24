<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Models\Product;

final class ReviewController
{
    public function store(): void
    {
        $user = current_user();
        $productId = (int) Request::input('product_id');
        $product = Product::findActive($productId);
        $rating = (int) Request::input('rating');
        $body = trim((string) Request::input('body', ''));
        $title = mb_substr(trim((string) Request::input('title', '')), 0, 150) ?: null;
        $back = $product ? '/product/' . $product['slug'] . '#reviews' : '/';
        if (!$product || $rating < 1 || $rating > 5 || mb_strlen($body) < 10 || mb_strlen($body) > 2000) {
            Session::flash('error', 'Please choose a rating and write at least 10 characters.');
            redirect($back);
        }
        if (!RateLimiter::hit('review:' . $user['id'], 3, 86400)) {
            Session::flash('error', 'You have reached the daily review limit.');
            redirect($back);
        }
        $order = DB::one('SELECT o.id FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status NOT IN ("cancelled","pending_payment") ORDER BY o.id DESC LIMIT 1', [$user['id'], $productId]);
        DB::insert('product_reviews', [
            'product_id' => $productId,
            'user_id' => $user['id'],
            'order_id' => $order['id'] ?? null,
            'reviewer_name' => $user['name'],
            'rating' => $rating,
            'title' => $title,
            'body' => $body,
            'is_verified_purchase' => $order ? 1 : 0,
            'status' => 'pending',
        ]);
        Session::flash('success', 'Thank you! Your review has been submitted and will appear after moderation.');
        redirect($back);
    }
}
