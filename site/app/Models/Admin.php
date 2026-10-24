<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use App\Core\Session;

final class Admin
{
    private static ?array $current = null;
    private static bool $resolved = false;

    public static function current(): ?array
    {
        if (!self::$resolved) {
            self::$resolved = true;
            $id = (int) Session::get('admin_id', 0);
            self::$current = $id ? DB::one('SELECT id, name, email FROM admins WHERE id = ? AND is_active = 1', [$id]) : null;
        }
        return self::$current;
    }

    public static function count(): int
    {
        return (int) DB::value('SELECT COUNT(*) FROM admins');
    }

    public static function findByEmail(string $email): ?array
    {
        return DB::one('SELECT * FROM admins WHERE email = ? AND is_active = 1', [strtolower($email)]);
    }

    public static function create(string $name, string $email, string $password): int
    {
        return DB::insert('admins', ['name' => $name, 'email' => strtolower($email), 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }

    public static function login(array $admin): void
    {
        Session::regenerate();
        Session::set('admin_id', (int) $admin['id']);
        DB::run('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
        self::$resolved = false;
    }

    public static function logout(): void
    {
        Session::forget('admin_id');
        Session::regenerate();
        self::$current = null;
    }
}
