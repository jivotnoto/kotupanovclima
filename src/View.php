<?php

declare(strict_types=1);

final class View
{
    public function __construct(
        private readonly string $viewsDirectory,
    ) {
    }

    public function render(string $template, array $data = [], string $layout = 'layout'): string
    {
        $templatePath = $this->viewsDirectory . DIRECTORY_SEPARATOR . $template . '.php';
        $layoutPath = $this->viewsDirectory . DIRECTORY_SEPARATOR . $layout . '.php';

        if (!is_file($templatePath)) {
            throw new RuntimeException('Липсва шаблон: ' . $templatePath);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $templatePath;
        $content = ob_get_clean();

        ob_start();
        require $layoutPath;

        return (string) ob_get_clean();
    }

    public function partial(string $template, array $data = []): void
    {
        $templatePath = $this->viewsDirectory . DIRECTORY_SEPARATOR . $template . '.php';
        extract($data, EXTR_SKIP);
        require $templatePath;
    }
}
