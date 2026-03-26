<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /**
     * @var array<string, array<string, array{handler: callable|array{0: class-string, 1: string}, middleware: list<class-string>}>>
     */
    private array $routes = [];

    /** @param list<class-string> $middleware */
    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    /** @param list<class-string> $middleware */
    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    /** @param list<class-string> $middleware */
    public function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): void
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = $this->normalizePath($path);

        $this->routes[$normalizedMethod][$normalizedPath] = [
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = $this->normalizePath((string) parse_url($uri, PHP_URL_PATH));

        $route = $this->routes[$normalizedMethod][$normalizedPath] ?? null;

        if ($route === null) {
            http_response_code(404);
            echo '404 Not Found';

            return;
        }

        foreach ($route['middleware'] as $middlewareClass) {
            /** @var \App\Core\MiddlewareInterface $middleware */
            $middleware = new $middlewareClass();
            $result = $middleware->handle();

            if ($result !== null) {
                echo $result;

                return;
            }
        }

        try {
            $response = $this->resolveHandler($route['handler'])();
        } catch (\RuntimeException) {
            http_response_code(500);
            echo '500 Internal Server Error';

            return;
        }

        if (is_string($response)) {
            echo $response;
        }
    }

    private function resolveHandler(callable|array $handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (
            is_array($handler)
            && count($handler) === 2
            && is_string($handler[0])
            && is_string($handler[1])
            && class_exists($handler[0])
        ) {
            $controller = new $handler[0]();
            $callable = [$controller, $handler[1]];

            if (is_callable($callable)) {
                return $callable;
            }
        }

        throw new \RuntimeException('Invalid route handler.');
    }

    private function normalizePath(?string $path): string
    {
        if ($path === null || $path === '') {
            return '/';
        }

        $trimmedPath = '/' . trim($path, '/');

        return $trimmedPath === '//' ? '/' : $trimmedPath;
    }
}