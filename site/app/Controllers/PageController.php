<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Product;
use App\Models\Setting;

final class PageController
{
    private function policy(string $slug, string $title, string $settingKey, string $intro = ''): void
    {
        View::show('page', [
            'title' => $title,
            'intro' => $intro,
            'body' => setting($settingKey, ''),
            'meta' => ['title' => $title . ' | ' . setting('store_name'), 'canonical' => url($slug), 'description' => $title . ' for ' . setting('store_name') . '.'],
        ]);
    }

    public function about(): void
    {
        View::show('about', ['meta' => ['title' => 'About Us | ' . setting('store_name'), 'canonical' => url('about'), 'description' => 'About Long Live Men — premium men\'s wellness.']]);
    }

    public function faq(): void
    {
        View::show('faq', ['faqs' => Setting::faqs(), 'meta' => ['title' => 'FAQs | ' . setting('store_name'), 'canonical' => url('faq'), 'description' => 'Frequently asked questions about the Long Live Men combo, shipping, payments and tracking.']]);
    }

    public function shipping(): void
    {
        $this->policy('shipping-policy', 'Shipping Policy', 'shipping_policy');
    }

    public function returns(): void
    {
        $this->policy('return-policy', 'Return & Refund Policy', 'return_policy');
    }

    public function privacy(): void
    {
        $this->policy('privacy-policy', 'Privacy Policy', 'privacy_policy');
    }

    public function terms(): void
    {
        $this->policy('terms', 'Terms & Conditions', 'terms_text');
    }

    public function disclaimer(): void
    {
        $this->policy('disclaimer', 'Disclaimer', 'disclaimer_text', setting('medical_disclaimer', ''));
    }

    public function contact(): void
    {
        View::show('contact', ['errors' => Session::get('_errors', []), 'meta' => ['title' => 'Contact Us | ' . setting('store_name'), 'canonical' => url('contact'), 'description' => 'Get in touch with Long Live Men customer support.']]);
        Session::forget('_errors');
    }

    public function contactSubmit(): void
    {
        if (!RateLimiter::hit('contact:' . Request::ip(), 5, 3600)) {
            Session::flash('error', 'Too many messages. Please try again later.');
            redirect('/contact');
        }
        $name = trim((string) Request::input('name', ''));
        $email = strtolower(trim((string) Request::input('email', '')));
        $phone = preg_replace('/\D/', '', (string) Request::input('phone', '')) ?: null;
        $subject = mb_substr(trim((string) Request::input('subject', '')), 0, 190) ?: null;
        $message = trim((string) Request::input('message', ''));
        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (mb_strlen($message) < 10) {
            $errors['message'] = 'Please write at least 10 characters.';
        }
        if ((string) Request::input('website', '') !== '') {
            $errors['message'] = 'Spam detected.';
        }
        if ($errors) {
            Session::set('_errors', $errors);
            Session::set('_old', $_POST);
            redirect('/contact');
        }
        DB::insert('contact_messages', compact('name', 'email', 'phone', 'subject', 'message'));
        if ($support = setting('support_email')) {
            Mailer::send($support, 'New contact message from ' . $name, '<p>' . nl2br(e($message)) . '</p><p>From: ' . e($name) . ' &lt;' . e($email) . '&gt; ' . e($phone) . '</p>');
        }
        Session::flash('success', 'Thanks! Your message has been received. We will get back to you soon.');
        redirect('/contact');
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $urls = ['', 'shop', 'about', 'contact', 'faq', 'shipping-policy', 'return-policy', 'privacy-policy', 'terms', 'disclaimer', 'track'];
        echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            echo '<url><loc>' . e(url($u)) . '</loc><changefreq>weekly</changefreq></url>';
        }
        foreach (Product::allActive() as $p) {
            echo '<url><loc>' . e(url('product/' . $p['slug'])) . '</loc><lastmod>' . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod><priority>1.0</priority></url>';
        }
        echo '</urlset>';
        exit;
    }

    public function health(): void
    {
        $db = false;
        try {
            $db = (bool) DB::value('SELECT 1');
        } catch (\Throwable) {
        }
        json_response(['ok' => $db, 'db' => $db, 'php' => PHP_VERSION, 'time' => date('c')], $db ? 200 : 500);
    }
}
