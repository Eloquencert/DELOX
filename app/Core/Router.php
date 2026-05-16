<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $this->pathToPattern($path),
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }

            // Extract named URL params (e.g. :id → $matches['id'])
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            [$controllerClass, $action] = $route['handler'];

            if (!class_exists($controllerClass)) {
                throw new RuntimeException("Controller {$controllerClass} not found.");
            }

            $controller = new $controllerClass($request, $response);
            $controller->$action(...array_values($params));
            return;
        }

        $response->setStatusCode(404)->view('errors/404');
    }

    private function pathToPattern(string $path): string
    {
        // Convert :param segments into named regex groups
        $pattern = preg_replace('#:([a-zA-Z_]+)#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
