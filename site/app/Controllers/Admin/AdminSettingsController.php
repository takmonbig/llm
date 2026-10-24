<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\Setting;
use App\Services\RazorpayService;
use App\Services\UploadService;

final class AdminSettingsController
{
    public const TEXT_KEYS = ['store_name', 'store_tagline', 'support_email', 'support_phone', 'business_address', 'announcement_text', 'announcement_extra', 'seo_title', 'seo_description', 'social_instagram', 'social_facebook', 'social_youtube', 'flat_shipping_fee'];
    public const LONG_KEYS = ['medical_disclaimer', 'about_text', 'shipping_policy', 'return_policy', 'privacy_policy', 'terms_text', 'disclaimer_text'];
    public const BOOL_KEYS = ['cod_enabled', 'razorpay_enabled', 'free_shipping'];

    public function index(): void
    {
        View::show('admin/settings', [
            'settings' => Setting::all(),
            'faqs' => Setting::faqs(),
            'razorpay' => ['configured' => RazorpayService::configured(), 'mode' => RazorpayService::mode(), 'key' => RazorpayService::keyId() ? substr(RazorpayService::keyId(), 0, 12) . '…' : '', 'webhook' => (string) env('RAZORPAY_WEBHOOK_SECRET', '') !== ''],
            'mail' => ['driver' => env('MAIL_DRIVER', 'log'), 'host' => env('MAIL_HOST', '')],
            'meta' => ['title' => 'Settings'],
        ], 'admin');
    }

    public function save(): void
    {
        foreach (self::TEXT_KEYS as $k) {
            Setting::set($k, mb_substr(trim((string) Request::input($k, '')), 0, 500));
        }
        foreach (self::LONG_KEYS as $k) {
            Setting::set($k, trim((string) Request::input($k, '')));
        }
        foreach (self::BOOL_KEYS as $k) {
            Setting::set($k, Request::input($k) ? '1' : '0');
        }
        $faqs = [];
        $qs = (array) Request::input('faq_q', []);
        $as = (array) Request::input('faq_a', []);
        foreach ($qs as $i => $q) {
            $q = trim((string) $q);
            $a = trim((string) ($as[$i] ?? ''));
            if ($q !== '' && $a !== '') {
                $faqs[] = ['q' => mb_substr($q, 0, 200), 'a' => mb_substr($a, 0, 2000)];
            }
        }
        Setting::set('faq_json', json_encode($faqs, JSON_UNESCAPED_UNICODE));
        Session::flash('success', 'Settings saved.');
        redirect('/admin/settings');
    }

    public function uploadLogo(): void
    {
        try {
            Setting::set('store_logo', UploadService::storeImage($_FILES['logo'] ?? null, 'brand'));
            Session::flash('success', 'Logo updated.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }
        redirect('/admin/settings');
    }

    public function messages(): void
    {
        View::show('admin/messages', ['messages' => DB::all('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 200'), 'meta' => ['title' => 'Messages']], 'admin');
    }

    public function markRead(string $id): void
    {
        DB::update('contact_messages', ['is_read' => 1], 'id = ?', [(int) $id]);
        redirect('/admin/messages');
    }
}
