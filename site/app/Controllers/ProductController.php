<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Request;
use App\Core\View;
use App\Models\Product;

final class ProductController
{
    public function index(): void
    {
        $q = mb_substr((string) Request::query('q', ''), 0, 80);
        $products = Product::allActive($q);
        foreach ($products as &$p) {
            $p['image'] = Product::primaryImage((int) $p['id']);
        }
        unset($p);
        View::show('shop', [
            'products' => $products,
            'q' => $q,
            'meta' => ['title' => ($q ? 'Search: ' . $q : 'Shop') . ' | ' . setting('store_name'), 'description' => 'Shop Long Live Men wellness combos.', 'canonical' => url('shop')],
        ]);
    }

    public function show(string $slug): void
    {
        $product = Product::findBySlug($slug);
        if (!$product) {
            abort(404);
        }
        $images = Product::images((int) $product['id']);
        $reviews = Product::approvedReviews((int) $product['id']);
        $rating = Product::ratingSummary((int) $product['id']);
        $user = current_user();
        $canReview = false;
        if ($user) {
            $canReview = (bool) DB::value('SELECT COUNT(*) FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.status IN ("delivered","shipped","confirmed","processing","placed")', [$user['id'], $product['id']]);
        }
        View::show('product', [
            'product' => $product,
            'images' => $images,
            'variants' => Product::variants((int) $product['id']),
            'reviews' => $reviews,
            'rating' => $rating,
            'canReview' => $canReview,
            'meta' => [
                'title' => $product['seo_title'] ?: $product['title'] . ' | ' . setting('store_name'),
                'description' => $product['seo_description'] ?: mb_substr((string) $product['short_description'], 0, 160),
                'canonical' => url('product/' . $product['slug']),
                'image' => product_image_url($images[0]['path'] ?? null),
                'type' => 'product',
            ],
            'bodyClass' => 'page-product',
        ]);
    }
}
