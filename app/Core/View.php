<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $content = $this->renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }

        return $this->renderFile($layout, array_merge($data, [
            'content' => $content,
        ]));
    }

    public function component(string $component, array $data = []): string
    {
        return $this->renderFile('components/' . $component, $data);
    }

    public function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function renderFile(string $view, array $data): string
    {
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException('View not found: ' . $view);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;

        return (string) ob_get_clean();
    }
}