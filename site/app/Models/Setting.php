<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Setting
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (DB::all('SELECT setting_key, setting_value FROM store_settings') as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, mixed $default = ''): mixed
    {
        $all = self::all();
        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '' ? $all[$key] : $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $v = self::get($key, null);
        return $v === null ? $default : in_array((string) $v, ['1', 'true', 'on'], true);
    }

    public static function set(string $key, ?string $value): void
    {
        DB::run('INSERT INTO store_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)', [$key, $value]);
        self::$cache = null;
    }

    public static function faqs(): array
    {
        $json = self::get('faq_json', '[]');
        $items = json_decode((string) $json, true);
        return is_array($items) ? $items : [];
    }
}
