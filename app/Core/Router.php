<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /**
     * @var array<string, array<string, callable|array{0: class-string, 1: string}>>
     */
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function addRoute(string $method, string $path, callable|array $handler): void
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = $this->normalizePath($path);

        $this->routes[$normalizedMethod][$normalizedPath] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = $this->normalizePath((string) parse_url($uri, PHP_URL_PATH));

        $handler = $this->routes[$normalizedMethod][$normalizedPath] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo '404 Not Found';

            return;
        }

        try {
            $response = $this->resolveHandler($handler)();
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