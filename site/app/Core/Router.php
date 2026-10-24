<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, array $handler, array $opts = []): void
    {
        $this->add('GET', $pattern, $handler, $opts);
    }

    public function post(string $pattern, array $handler, array $opts = []): void
    {
        $this->add('POST', $pattern, $handler, $opts);
    }

    private function add(string $method, string $pattern, array $handler, array $opts): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', rtrim($pattern, '/') ?: '/') . '/?$#';
        $this->routes[] = compact('method', 'regex', 'handler', 'opts');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');
        $base = parse_url(app_url(), PHP_URL_PATH) ?: '';
        if ($base && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method || !preg_match($route['regex'], $path, $m)) {
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            $this->applyMiddleware($route['opts'], $method);
            [$class, $action] = $route['handler'];
            $controller = new $class();
            $controller->$action(...array_values($params));
            return;
        }
        abort(404);
    }

    private function applyMiddleware(array $opts, string $method): void
    {
        $mw = $opts['middleware'] ?? [];
        if ($method === 'POST' && !in_array('nocsrf', $mw, true)) {
            $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
            if (!Session::verifyCsrf($token)) {
                if (Request::wantsJson()) {
                    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
                }
                Session::flash('error', 'Your session expired. Please try again.');
                redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
        if (in_array('auth', $mw, true) && !current_user()) {
            Session::set('_intended', $_SERVER['REQUEST_URI'] ?? '/account');
            if (Request::wantsJson()) {
                json_response(['ok' => false, 'error' => 'Please log in to continue.', 'redirect' => url('login')], 401);
            }
            redirect('/login');
        }
        if (in_array('admin', $mw, true) && !current_admin()) {
            redirect('/admin/login');
        }
        if (in_array('guest', $mw, true) && current_user()) {
            redirect('/account');
        }
    }
}
