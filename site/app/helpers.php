<?php
declare(strict_types=1);

use App\Core\Env;
use App\Core\Session;
use App\Models\Setting;

function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

function env_bool(string $key, bool $default = false): bool
{
    $v = Env::get($key);
    if ($v === null) {
        return $default;
    }
    return in_array(strtolower((string) $v), ['1', 'true', 'yes', 'on'], true);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function nl2p(?string $text): string
{
    $parts = preg_split('/\n{2,}/', trim((string) $text)) ?: [];
    return implode('', array_map(fn($p) => '<p>' . nl2br(e($p)) . '</p>', array_filter($parts, fn($p) => trim($p) !== '')));
}

function app_url(): string
{
    return rtrim((string) env('APP_URL', ''), '/');
}

function url(string $path = ''): string
{
    return app_url() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT_PATH . '/' . ltrim($path, '/');
    $v = is_file($file) ? '?v=' . substr(md5((string) filemtime($file)), 0, 8) : '';
    return url($path) . $v;
}

function is_https(): bool
{
    return str_starts_with(app_url(), 'https://') || (($_SERVER['HTTPS'] ?? '') === 'on') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function money(float|int|string $amount, bool $decimals = false): string
{
    $amount = (float) $amount;
    $formatted = $decimals || floor($amount) != $amount ? number_format($amount, 2) : number_format($amount, 0);
    return '₹' . $formatted;
}

function setting(string $key, mixed $default = ''): mixed
{
    return Setting::get($key, $default);
}

function csrf_token(): string
{
    return Session::csrfToken();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function old(string $key, mixed $default = ''): string
{
    $old = Session::get('_old', []);
    return e($old[$key] ?? $default);
}

function flash(string $key): ?string
{
    return Session::flashGet($key);
}

function current_user(): ?array
{
    return App\Models\User::current();
}

function current_admin(): ?array
{
    return App\Models\Admin::current();
}

function redirect(string $to, int $code = 302): never
{
    if (!preg_match('#^https?://#', $to)) {
        $to = url($to);
    }
    header('Location: ' . $to, true, $code);
    exit;
}

function abort(int $code = 404, string $message = ''): never
{
    http_response_code($code);
    $view = $code === 404 ? 'errors/404' : 'errors/500';
    echo App\Core\View::render($view, ['message' => $message], 'main');
    exit;
}

function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? '';
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text) ?: $text;
    $text = preg_replace('~[^-\w]+~', '', $text) ?? '';
    return strtolower(trim($text, '-')) ?: 'item';
}

function order_status_label(string $status): string
{
    return match ($status) {
        'pending_payment' => 'Awaiting Payment',
        default => ucfirst(str_replace('_', ' ', $status)),
    };
}

function format_date(?string $dt, string $format = 'd M Y, h:i A'): string
{
    if (!$dt) {
        return '—';
    }
    return date($format, strtotime($dt));
}

function product_image_url(?string $path): string
{
    if (!$path) {
        return asset('assets/images/products/combo.png');
    }
    return str_starts_with($path, 'http') ? $path : url($path);
}

function image_webp(string $path): ?string
{
    $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
    return $webp !== $path && is_file(ROOT_PATH . '/' . $webp) ? url($webp) : null;
}
