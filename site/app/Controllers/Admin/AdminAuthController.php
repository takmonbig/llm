<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Admin;

final class AdminAuthController
{
    public function setupForm(): void
    {
        if (Admin::count() > 0) {
            redirect('/admin/login');
        }
        View::show('admin/setup', ['meta' => ['title' => 'Admin setup', 'robots' => 'noindex']], 'admin-auth');
    }

    public function setup(): void
    {
        if (Admin::count() > 0) {
            redirect('/admin/login');
        }
        if (!RateLimiter::hit('admin-setup:' . Request::ip(), 5, 900)) {
            Session::flash('error', 'Too many attempts. Please wait.');
            redirect('/admin/setup');
        }
        $key = (string) env('ADMIN_SETUP_KEY', '');
        if ($key === '' || !hash_equals($key, (string) Request::input('setup_key', ''))) {
            Session::flash('error', 'Invalid setup key. Check ADMIN_SETUP_KEY in your .env file.');
            redirect('/admin/setup');
        }
        $name = trim((string) Request::input('name', ''));
        $email = strtolower(trim((string) Request::input('email', '')));
        $password = (string) Request::input('password', '');
        if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            Session::flash('error', 'Enter a name, a valid email and a password of at least 10 characters.');
            redirect('/admin/setup');
        }
        $id = Admin::create($name, $email, $password);
        Admin::login(['id' => $id]);
        Session::flash('success', 'Administrator account created. You can now remove ADMIN_SETUP_KEY from .env.');
        redirect('/admin');
    }

    public function loginForm(): void
    {
        if (Admin::count() === 0) {
            redirect('/admin/setup');
        }
        if (current_admin()) {
            redirect('/admin');
        }
        View::show('admin/login', ['meta' => ['title' => 'Admin login', 'robots' => 'noindex']], 'admin-auth');
    }

    public function login(): void
    {
        $email = strtolower(trim((string) Request::input('email', '')));
        if (!RateLimiter::hit('admin-login:' . Request::ip(), 8, 900)) {
            Session::flash('error', 'Too many attempts. Please wait 15 minutes.');
            redirect('/admin/login');
        }
        $admin = Admin::findByEmail($email);
        if (!$admin || !password_verify((string) Request::input('password', ''), $admin['password_hash'])) {
            Session::flash('error', 'Incorrect email or password.');
            redirect('/admin/login');
        }
        RateLimiter::clear('admin-login:' . Request::ip());
        Admin::login($admin);
        redirect('/admin');
    }

    public function logout(): void
    {
        Admin::logout();
        redirect('/admin/login');
    }
}
