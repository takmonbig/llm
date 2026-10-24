<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Product
{
    public static function find(int $id): ?array
    {
        return DB::one('SELECT * FROM products WHERE id = ?', [$id]);
    }

    public static function findActive(int $id): ?array
    {
        return DB::one('SELECT * FROM products WHERE id = ? AND is_active = 1', [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return DB::one('SELECT * FROM products WHERE slug = ? AND is_active = 1', [$slug]);
    }

    public static function featured(): ?array
    {
        return DB::one('SELECT * FROM products WHERE is_active = 1 ORDER BY is_featured DESC, id ASC LIMIT 1');
    }

    public static function allActive(string $q = ''): array
    {
        if ($q !== '') {
            $like = '%' . $q . '%';
            return DB::all('SELECT * FROM products WHERE is_active = 1 AND (title LIKE ? OR subtitle LIKE ? OR short_description LIKE ?) ORDER BY is_featured DESC, id ASC', [$like, $like, $like]);
        }
        return DB::all('SELECT * FROM products WHERE is_active = 1 ORDER BY is_featured DESC, id ASC');
    }

    public static function all(): array
    {
        return DB::all('SELECT p.*, (SELECT path FROM product_images WHERE product_id = p.id ORDER BY sort_order LIMIT 1) AS image FROM products p ORDER BY id ASC');
    }

    public static function images(int $productId): array
    {
        return DB::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$productId]);
    }

    public static function primaryImage(int $productId): ?string
    {
        $v = DB::value('SELECT path FROM product_images WHERE product_id = ? ORDER BY sort_order, id LIMIT 1', [$productId]);
        return $v === false ? null : $v;
    }

    public static function variants(int $productId): array
    {
        return DB::all('SELECT * FROM product_variants WHERE product_id = ? ORDER BY sort_order, id', [$productId]);
    }

    public static function approvedReviews(int $productId): array
    {
        return DB::all('SELECT * FROM product_reviews WHERE product_id = ? AND status = "approved" ORDER BY created_at DESC LIMIT 30', [$productId]);
    }

    public static function ratingSummary(int $productId): array
    {
        $row = DB::one('SELECT COUNT(*) AS cnt, AVG(rating) AS avg FROM product_reviews WHERE product_id = ? AND status = "approved"', [$productId]);
        return ['count' => (int) ($row['cnt'] ?? 0), 'avg' => $row['avg'] ? round((float) $row['avg'], 1) : null];
    }
}
