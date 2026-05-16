<?php

declare(strict_types=1);

namespace App\Core;

class Application
{
    private Router   $router;
    private Request  $request;
    private Response $response;

    public function __construct()
    {
        $this->request  = new Request();
        $this->response = new Response();
        $this->router   = new Router();
    }

    public function run(): void
    {
        $this->registerRoutes();
        $this->router->dispatch($this->request, $this->response);
    }

    private function registerRoutes(): void
    {
        $router = $this->router;
        require ROUTES_PATH . '/web.php';
        require ROUTES_PATH . '/api.php';
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
