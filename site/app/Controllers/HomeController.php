<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Models\Product;
use App\Models\Setting;

final class HomeController
{
    public function index(): void
    {
        $product = Product::featured();
        $data = [
            'product' => $product,
            'variants' => $product ? Product::variants((int) $product['id']) : [],
            'reviews' => $product ? Product::approvedReviews((int) $product['id']) : [],
            'rating' => $product ? Product::ratingSummary((int) $product['id']) : ['count' => 0, 'avg' => null],
            'faqs' => Setting::faqs(),
            'meta' => [
                'title' => setting('seo_title', 'Long Live Men'),
                'description' => setting('seo_description', ''),
                'canonical' => url('/'),
                'type' => 'website',
            ],
            'bodyClass' => 'page-home',
        ];
        View::show('home', $data);
    }
}
