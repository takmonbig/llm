<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Order;

final class AdminOrderController
{
    private function filters(): array
    {
        $where = ['1=1'];
        $params = [];
        $q = trim((string) Request::query('q', ''));
        if ($q !== '') {
            $where[] = '(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.email LIKE ? OR o.phone LIKE ?)';
            array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
        }
        $status = (string) Request::query('status', '');
        if (in_array($status, Order::STATUSES, true)) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }
        $pm = (string) Request::query('payment', '');
        if (in_array($pm, ['cod', 'razorpay'], true)) {
            $where[] = 'o.payment_method = ?';
            $params[] = $pm;
        }
        $from = (string) Request::query('from', '');
        if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $where[] = 'o.created_at >= ?';
            $params[] = $from . ' 00:00:00';
        }
        $to = (string) Request::query('to', '');
        if ($to && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $where[] = 'o.created_at <= ?';
            $params[] = $to . ' 23:59:59';
        }
        return [implode(' AND ', $where), $params, compact('q', 'status', 'pm', 'from', 'to')];
    }

    public function index(): void
    {
        [$where, $params, $f] = $this->filters();
        $page = max(1, (int) Request::query('page', 1));
        $per = 25;
        $total = (int) DB::value("SELECT COUNT(*) FROM orders o WHERE $where", $params);
        $orders = DB::all("SELECT o.*, (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) AS item_count FROM orders o WHERE $where ORDER BY o.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
        View::show('admin/orders/index', ['orders' => $orders, 'filters' => $f, 'total' => $total, 'page' => $page, 'pages' => (int) ceil($total / $per), 'meta' => ['title' => 'Orders']], 'admin');
    }

    public function export(): void
    {
        [$where, $params] = $this->filters();
        $rows = DB::all("SELECT o.* FROM orders o WHERE $where ORDER BY o.id DESC", $params);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="orders-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['Order Number', 'Date', 'Customer', 'Email', 'Phone', 'Address', 'City', 'State', 'PIN', 'Items', 'Subtotal', 'Shipping', 'Total', 'Payment Method', 'Payment Status', 'Order Status', 'Tracking']);
        foreach ($rows as $o) {
            $items = implode(' | ', array_map(fn($i) => $i['product_title'] . ' x' . $i['quantity'], Order::items((int) $o['id'])));
            $csvSafe = fn($v) => preg_match('/^[=+\-@]/', (string) $v) ? "'" . $v : $v;
            fputcsv($out, array_map($csvSafe, [$o['order_number'], $o['created_at'], $o['customer_name'], $o['email'], $o['phone'], trim($o['ship_line1'] . ' ' . $o['ship_line2'] . ' ' . $o['ship_landmark']), $o['ship_city'], $o['ship_state'], $o['ship_pincode'], $items, $o['subtotal'], $o['shipping_fee'], $o['total'], $o['payment_method'], $o['payment_status'], $o['status'], trim(($o['tracking_courier'] ?? '') . ' ' . ($o['tracking_number'] ?? ''))]));
        }
        fclose($out);
        exit;
    }

    public function show(string $id): void
    {
        $order = Order::find((int) $id) ?: abort(404);
        View::show('admin/orders/show', [
            'order' => $order,
            'items' => Order::items((int) $id),
            'history' => Order::history((int) $id),
            'payments' => Order::payments((int) $id),
            'nextStatuses' => Order::TRANSITIONS[$order['status']] ?? [],
            'meta' => ['title' => 'Order ' . $order['order_number']],
        ], 'admin');
    }

    public function updateStatus(string $id): void
    {
        $order = Order::find((int) $id) ?: abort(404);
        $to = (string) Request::input('status', '');
        $note = mb_substr(trim((string) Request::input('note', '')), 0, 500) ?: null;
        $admin = current_admin();
        $ok = DB::tx(fn() => Order::setStatus((int) $id, $to, 'admin:' . $admin['email'], $note));
        if ($ok) {
            if ($to === 'delivered' && $order['payment_method'] === 'cod' && $order['payment_status'] !== 'paid') {
                DB::update('orders', ['payment_status' => 'paid'], 'id = ?', [(int) $id]);
            }
            Session::flash('success', 'Status updated to ' . order_status_label($to) . '.');
        } else {
            Session::flash('error', 'That status change is not allowed from ' . order_status_label($order['status']) . '.');
        }
        redirect('/admin/orders/' . $id);
    }

    public function updateTracking(string $id): void
    {
        Order::find((int) $id) ?: abort(404);
        $url = trim((string) Request::input('tracking_url', ''));
        DB::update('orders', [
            'tracking_courier' => mb_substr(trim((string) Request::input('tracking_courier', '')), 0, 100) ?: null,
            'tracking_number' => mb_substr(trim((string) Request::input('tracking_number', '')), 0, 100) ?: null,
            'tracking_url' => $url !== '' && filter_var($url, FILTER_VALIDATE_URL) && str_starts_with($url, 'http') ? mb_substr($url, 0, 255) : null,
            'admin_note' => trim((string) Request::input('admin_note', '')) ?: null,
        ], 'id = ?', [(int) $id]);
        Session::flash('success', 'Tracking details saved.');
        redirect('/admin/orders/' . $id);
    }
}
