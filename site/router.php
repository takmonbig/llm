<?php
// Router for PHP's built-in dev server only (php -S). Apache uses .htaccess instead.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file) && !str_ends_with($file, '.php')) {
    $blocked = ['/app/', '/storage/', '/database/', '/.env', '/scripts/'];
    foreach ($blocked as $b) {
        if (str_starts_with($path, $b)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $types = ['webp' => 'image/webp', 'avif' => 'image/avif', 'svg' => 'image/svg+xml', 'woff2' => 'font/woff2', 'webmanifest' => 'application/manifest+json', 'xml' => 'application/xml', 'txt' => 'text/plain'];
    if (isset($types[$ext])) {
        header('Content-Type: ' . $types[$ext]);
        header('Cache-Control: public, max-age=86400');
        readfile($file);
        exit;
    }
    return false;
}
require __DIR__ . '/index.php';
