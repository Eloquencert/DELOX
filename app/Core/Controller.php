<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(
        protected Request  $request,
        protected Response $response,
    ) {}

    protected function view(string $template, array $data = []): void
    {
        $this->response->view($template, $data);
    }

    protected function json(array $data, int $statusCode = 200): never
    {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url): never
    {
        $this->response->redirect($url);
    }
}
