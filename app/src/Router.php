<?php
declare(strict_types=1);

namespace App;

final class Router
{
    private array $routes = [];
    private static string $current = '/';

    public function get(string $path, callable|array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function any(string $path, callable|array $handler): self
    {
        $this->add('GET', $path, $handler);
        return $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): self
    {
        $regex = '#^' . preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[A-Za-z0-9_-]+)', rtrim($path, '/') ?: '/') . '$#';
        $this->routes[$method][] = [$regex, $handler];
        return $this;
    }

    public static function currentPath(): string
    {
        return self::$current;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rawurldecode((string)parse_url($uri, PHP_URL_PATH));
        $base = base_path();
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . trim($path, '/');
        if ($path === '/index.php') {
            $path = '/';
        }
        self::$current = $path;

        foreach ($this->routes[$method] ?? [] as [$regex, $handler]) {
            if (preg_match($regex, $path, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    (new $class())->$action(...$params);
                } else {
                    $handler(...$params);
                }
                return;
            }
        }
        abort(404);
    }
}
