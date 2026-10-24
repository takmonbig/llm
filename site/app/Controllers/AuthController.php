<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\User;
use App\Services\CartService;

final class AuthController
{
    public function loginForm(): void
    {
        View::show('auth/login', ['meta' => ['title' => 'Login | ' . setting('store_name'), 'robots' => 'noindex']]);
    }

    public function login(): void
    {
        $email = strtolower(trim((string) Request::input('email', '')));
        $password = (string) Request::input('password', '');
        if (!RateLimiter::hit('login:' . Request::ip(), 10, 900) || !RateLimiter::hit('login:' . $email, 6, 900)) {
            Session::flash('error', 'Too many login attempts. Please wait 15 minutes and try again.');
            redirect('/login');
        }
        $user = User::findByEmail($email);
        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Incorrect email or password.');
            Session::set('_old', ['email' => $email]);
            redirect('/login');
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            DB::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$user['id']]);
        }
        RateLimiter::clear('login:' . $email);
        User::login($user);
        CartService::cart();
        $intended = Session::get('_intended');
        Session::forget('_intended');
        redirect($intended && str_starts_with((string) $intended, '/') ? $intended : '/account');
    }

    public function registerForm(): void
    {
        View::show('auth/register', ['meta' => ['title' => 'Create Account | ' . setting('store_name'), 'robots' => 'noindex']]);
    }

    public function register(): void
    {
        if (!RateLimiter::hit('register:' . Request::ip(), 5, 3600)) {
            Session::flash('error', 'Too many registrations from this network. Please try later.');
            redirect('/register');
        }
        $name = trim((string) Request::input('name', ''));
        $email = strtolower(trim((string) Request::input('email', '')));
        $phone = preg_replace('/\D/', '', (string) Request::input('phone', '')) ?: null;
        $password = (string) Request::input('password', '');
        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif (User::findByEmail($email)) {
            $errors['email'] = 'An account with this email already exists. Try logging in.';
        }
        if ($phone && !preg_match('/^[6-9]\d{9}$/', $phone)) {
            $errors['phone'] = 'Enter a valid 10-digit mobile number.';
        }
        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if ($errors) {
            Session::set('_errors', $errors);
            Session::set('_old', ['name' => $name, 'email' => $email, 'phone' => $phone]);
            redirect('/register');
        }
        $id = User::create($name, $email, $password, $phone);
        User::login(User::find($id));
        CartService::cart();
        Session::flash('success', 'Welcome to Long Live Men! Your account is ready.');
        redirect('/account');
    }

    public function logout(): void
    {
        User::logout();
        Session::forget('cart_token');
        Session::flash('success', 'You have been logged out.');
        redirect('/');
    }

    public function forgotForm(): void
    {
        View::show('auth/forgot', ['meta' => ['title' => 'Forgot Password | ' . setting('store_name'), 'robots' => 'noindex']]);
    }

    public function forgot(): void
    {
        $email = strtolower(trim((string) Request::input('email', '')));
        if (RateLimiter::hit('forgot:' . Request::ip(), 5, 900)) {
            $user = User::findByEmail($email);
            if ($user) {
                $token = User::createResetToken((int) $user['id']);
                $link = url('reset-password/' . $token);
                Mailer::send($email, 'Reset your password', View::render('emails/password-reset', ['name' => $user['name'], 'link' => $link], null));
            }
        }
        Session::flash('success', 'If an account exists for that email, a password reset link has been sent. It expires in 1 hour.');
        redirect('/forgot-password');
    }

    public function resetForm(string $token): void
    {
        if (!User::findResetToken($token)) {
            Session::flash('error', 'This reset link is invalid or has expired. Please request a new one.');
            redirect('/forgot-password');
        }
        View::show('auth/reset', ['token' => $token, 'meta' => ['title' => 'Set a new password', 'robots' => 'noindex']]);
    }

    public function reset(): void
    {
        $token = (string) Request::input('token', '');
        $password = (string) Request::input('password', '');
        $row = User::findResetToken($token);
        if (!$row) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            redirect('/forgot-password');
        }
        if (strlen($password) < 8 || $password !== (string) Request::input('password_confirm', '')) {
            Session::flash('error', 'Passwords must match and be at least 8 characters.');
            redirect('/reset-password/' . $token);
        }
        DB::update('users', ['password_hash' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$row['user_id']]);
        DB::update('password_reset_tokens', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$row['id']]);
        Session::flash('success', 'Your password has been updated. Please log in.');
        redirect('/login');
    }
}
