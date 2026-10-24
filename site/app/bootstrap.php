<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('APP_START', microtime(true));

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

require APP_PATH . '/Core/Env.php';
App\Core\Env::load(ROOT_PATH . '/.env');
require APP_PATH . '/helpers.php';

date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));
mb_internal_encoding('UTF-8');

$debug = env_bool('APP_DEBUG', false);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');
error_reporting(E_ALL);

set_exception_handler(function (Throwable $e) use ($debug): void {
    App\Core\Logger::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTraceAsString()]);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if ($debug) {
        echo '<pre style="padding:24px;font:14px/1.5 monospace">' . e(get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        echo App\Core\View::render('errors/500', [], 'main');
    }
    exit;
});

App\Core\Session::start();
