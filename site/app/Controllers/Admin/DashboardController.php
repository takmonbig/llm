<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\View;

final class DashboardController
{
    public function index(): void
    {
        $stats = [
            'total_orders' => (int) DB::value('SELECT COUNT(*) FROM orders WHERE status <> "pending_payment"'),
            'total_sales' => (float) DB::value('SELECT COALESCE(SUM(total),0) FROM orders WHERE (payment_status = "paid" OR payment_method = "cod") AND status NOT IN ("cancelled","returned","pending_payment")'),
            'pending_orders' => (int) DB::value('SELECT COUNT(*) FROM orders WHERE status IN ("placed","confirmed","processing")'),
            'cod_orders' => (int) DB::value('SELECT COUNT(*) FROM orders WHERE payment_method = "cod" AND status <> "pending_payment"'),
            'paid_online' => (int) DB::value('SELECT COUNT(*) FROM orders WHERE payment_method = "razorpay" AND payment_status = "paid"'),
            'awaiting_payment' => (int) DB::value('SELECT COUNT(*) FROM orders WHERE status = "pending_payment"'),
            'pending_reviews' => (int) DB::value('SELECT COUNT(*) FROM product_reviews WHERE status = "pending"'),
            'unread_messages' => (int) DB::value('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
        ];
        View::show('admin/dashboard', [
            'stats' => $stats,
            'recent' => DB::all('SELECT * FROM orders ORDER BY id DESC LIMIT 8'),
            'products' => DB::all('SELECT id, title, stock, price, is_active FROM products ORDER BY id'),
            'meta' => ['title' => 'Dashboard'],
        ], 'admin');
    }
}
