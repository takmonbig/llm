<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;

final class AdminReviewController
{
    public function index(): void
    {
        $status = (string) Request::query('status', 'pending');
        $allowed = ['pending', 'approved', 'rejected', 'hidden', 'all'];
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }
        $sql = 'SELECT r.*, p.title AS product_title FROM product_reviews r JOIN products p ON p.id = r.product_id';
        $params = [];
        if ($status !== 'all') {
            $sql .= ' WHERE r.status = ?';
            $params[] = $status;
        }
        $reviews = DB::all($sql . ' ORDER BY r.id DESC LIMIT 200', $params);
        View::show('admin/reviews', ['reviews' => $reviews, 'status' => $status, 'meta' => ['title' => 'Reviews']], 'admin');
    }

    public function updateStatus(string $id): void
    {
        $to = (string) Request::input('status', '');
        if (in_array($to, ['approved', 'rejected', 'hidden', 'pending'], true)) {
            DB::update('product_reviews', ['status' => $to], 'id = ?', [(int) $id]);
            Session::flash('success', 'Review marked as ' . $to . '.');
        }
        redirect('/admin/reviews?status=' . ($_GET['status'] ?? 'pending'));
    }
}
