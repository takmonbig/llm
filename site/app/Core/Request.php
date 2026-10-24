<?php
declare(strict_types=1);

namespace App\Core;

final class Request
{
    private static ?array $json = null;

    public static function input(string $key, mixed $default = null): mixed
    {
        $src = self::isJson() ? self::json() : array_merge($_GET, $_POST);
        $v = $src[$key] ?? $default;
        return is_string($v) ? trim($v) : $v;
    }

    public static function all(): array
    {
        return self::isJson() ? self::json() : array_merge($_GET, $_POST);
    }

    public static function query(string $key, mixed $default = null): mixed
    {
        $v = $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $v;
    }

    public static function isJson(): bool
    {
        return str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    public static function json(): array
    {
        if (self::$json === null) {
            self::$json = json_decode(self::rawBody(), true) ?: [];
        }
        return self::$json;
    }

    public static function rawBody(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    public static function wantsJson(): bool
    {
        return self::isJson()
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public static function ip(): string
    {
        $fwd = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($fwd !== '') {
            return trim(explode(',', $fwd)[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }
}
