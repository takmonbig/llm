<?php
declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    /** Returns true when the request is allowed. */
    public static function hit(string $bucket, int $max, int $windowSeconds): bool
    {
        $bucket = substr($bucket, 0, 120);
        $row = DB::one('SELECT hits, window_start FROM rate_limits WHERE bucket = ?', [$bucket]);
        $now = time();
        if (!$row || strtotime($row['window_start']) + $windowSeconds < $now) {
            DB::run('REPLACE INTO rate_limits (bucket, hits, window_start) VALUES (?, 1, ?)', [$bucket, date('Y-m-d H:i:s', $now)]);
            return true;
        }
        if ((int) $row['hits'] >= $max) {
            return false;
        }
        DB::run('UPDATE rate_limits SET hits = hits + 1 WHERE bucket = ?', [$bucket]);
        return true;
    }

    public static function clear(string $bucket): void
    {
        DB::run('DELETE FROM rate_limits WHERE bucket = ?', [substr($bucket, 0, 120)]);
    }
}
