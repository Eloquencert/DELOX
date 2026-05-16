<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers  = [];

    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    public function setHeader(string $name, string $value): static
    {
        $this->headers[$name] = $value;
        header("$name: $value");
        return $this;
    }

    public function json(array $data, int $statusCode = 200): never
    {
        $this->setStatusCode($statusCode)
             ->setHeader('Content-Type', 'application/json');

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public function view(string $template, array $data = []): void
    {
        $layout = $data['layout'] ?? 'main';
        unset($data['layout']);

        extract($data);

        $content = $this->renderPartial($template, $data);

        require VIEWS_PATH . "/layouts/{$layout}.php";
    }

    public function partial(string $template, array $data = []): void
    {
        extract($data);
        require VIEWS_PATH . "/{$template}.php";
    }

    private function renderPartial(string $template, array $data): string
    {
        extract($data);
        ob_start();
        require VIEWS_PATH . "/{$template}.php";
        return ob_get_clean();
    }
}
