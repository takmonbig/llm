<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;

final class AccountController
{
    public function index(): void
    {
        $user = current_user();
        View::show('account/index', [
            'user' => $user,
            'orders' => array_slice(Order::forUser((int) $user['id']), 0, 3),
            'addressCount' => count(User::addresses((int) $user['id'])),
            'errors' => Session::get('_errors', []),
            'meta' => ['title' => 'My Account | ' . setting('store_name'), 'robots' => 'noindex'],
        ]);
        Session::forget('_errors');
    }

    public function updateProfile(): void
    {
        $user = current_user();
        $name = trim((string) Request::input('name', ''));
        $phone = preg_replace('/\D/', '', (string) Request::input('phone', '')) ?: null;
        if (mb_strlen($name) < 2 || ($phone && !preg_match('/^[6-9]\d{9}$/', $phone))) {
            Session::flash('error', 'Please enter a valid name and mobile number.');
            redirect('/account');
        }
        DB::update('users', ['name' => $name, 'phone' => $phone], 'id = ?', [$user['id']]);
        Session::flash('success', 'Profile updated.');
        redirect('/account');
    }

    public function updatePassword(): void
    {
        $user = User::find((int) current_user()['id']);
        $current = (string) Request::input('current_password', '');
        $new = (string) Request::input('new_password', '');
        if (!password_verify($current, $user['password_hash'])) {
            Session::flash('error', 'Your current password is incorrect.');
            redirect('/account');
        }
        if (strlen($new) < 8) {
            Session::flash('error', 'New password must be at least 8 characters.');
            redirect('/account');
        }
        DB::update('users', ['password_hash' => password_hash($new, PASSWORD_DEFAULT)], 'id = ?', [$user['id']]);
        Session::flash('success', 'Password changed.');
        redirect('/account');
    }

    public function addresses(): void
    {
        $user = current_user();
        View::show('account/addresses', [
            'user' => $user,
            'addresses' => User::addresses((int) $user['id']),
            'states' => OrderService::STATES,
            'errors' => Session::get('_errors', []),
            'meta' => ['title' => 'Saved Addresses | ' . setting('store_name'), 'robots' => 'noindex'],
        ]);
        Session::forget('_errors');
    }

    public function addAddress(): void
    {
        $user = current_user();
        $in = Request::all();
        $in['email'] = $user['email'];
        $in['payment_method'] = 'cod';
        [$errors, $clean] = OrderService::validate($in);
        unset($errors['payment_method'], $errors['email']);
        if ($errors) {
            Session::set('_errors', $errors);
            Session::set('_old', $_POST);
            redirect('/account/addresses');
        }
        if (!empty($in['is_default'])) {
            DB::run('UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?', [$user['id']]);
        }
        DB::insert('customer_addresses', [
            'user_id' => $user['id'],
            'label' => mb_substr(trim((string) ($in['label'] ?? '')), 0, 60) ?: null,
            'full_name' => $clean['customer_name'],
            'phone' => $clean['phone'],
            'line1' => $clean['ship_line1'],
            'line2' => $clean['ship_line2'],
            'landmark' => $clean['ship_landmark'],
            'city' => $clean['ship_city'],
            'state' => $clean['ship_state'],
            'pincode' => $clean['ship_pincode'],
            'is_default' => empty($in['is_default']) ? 0 : 1,
        ]);
        Session::flash('success', 'Address saved.');
        redirect('/account/addresses');
    }

    public function deleteAddress(string $id): void
    {
        DB::run('DELETE FROM customer_addresses WHERE id = ? AND user_id = ?', [(int) $id, current_user()['id']]);
        Session::flash('success', 'Address removed.');
        redirect('/account/addresses');
    }

    public function orders(): void
    {
        $user = current_user();
        View::show('account/orders', [
            'user' => $user,
            'orders' => Order::forUser((int) $user['id']),
            'meta' => ['title' => 'Order History | ' . setting('store_name'), 'robots' => 'noindex'],
        ]);
    }

    public function order(string $number): void
    {
        $user = current_user();
        $order = Order::findByNumber($number);
        if (!$order || (int) $order['user_id'] !== (int) $user['id']) {
            abort(404);
        }
        View::show('order-detail', [
            'order' => $order,
            'items' => Order::items((int) $order['id']),
            'history' => Order::history((int) $order['id']),
            'guest' => false,
            'meta' => ['title' => 'Order ' . $order['order_number'] . ' | ' . setting('store_name'), 'robots' => 'noindex'],
        ]);
    }
}
