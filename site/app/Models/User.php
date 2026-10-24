<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use App\Core\Session;

final class User
{
    private static ?array $current = null;
    private static bool $resolved = false;

    public static function current(): ?array
    {
        if (!self::$resolved) {
            self::$resolved = true;
            $id = (int) Session::get('user_id', 0);
            self::$current = $id ? DB::one('SELECT id, name, email, phone, created_at FROM users WHERE id = ? AND is_active = 1', [$id]) : null;
        }
        return self::$current;
    }

    public static function findByEmail(string $email): ?array
    {
        return DB::one('SELECT * FROM users WHERE email = ?', [strtolower($email)]);
    }

    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM users WHERE id = ?', [$id]);
    }

    public static function create(string $name, string $email, string $password, ?string $phone = null): int
    {
        return DB::insert('users', [
            'name' => $name,
            'email' => strtolower($email),
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        DB::run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
        self::$resolved = false;
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::regenerate();
        self::$current = null;
    }

    public static function createResetToken(int $userId): string
    {
        DB::run('DELETE FROM password_reset_tokens WHERE user_id = ? OR expires_at < NOW()', [$userId]);
        $token = bin2hex(random_bytes(32));
        DB::insert('password_reset_tokens', [
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);
        return $token;
    }

    public static function findResetToken(string $token): ?array
    {
        return DB::one('SELECT * FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()', [hash('sha256', $token)]);
    }

    public static function addresses(int $userId): array
    {
        return DB::all('SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC', [$userId]);
    }
}
