<?php

declare(strict_types=1);

final class Logger
{
    public function __construct(
        private readonly string $logDirectory,
    ) {
    }

    public function access(array $payload): void
    {
        $this->write('access.log', array_merge([
            'type' => 'access',
        ], $payload));
    }

    public function security(string $event, array $payload = [], string $severity = 'info'): void
    {
        $this->write('security.log', array_merge([
            'type' => 'security',
            'severity' => $severity,
            'event' => $event,
        ], $payload));
    }

    public function application(string $event, array $payload = [], string $severity = 'info'): void
    {
        $this->write('application.log', array_merge([
            'type' => 'application',
            'severity' => $severity,
            'event' => $event,
        ], $payload));
    }

    private function write(string $fileName, array $payload): void
    {
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0775, true);
        }

        $line = json_encode(
            array_merge([
                'timestamp' => gmdate('c'),
            ], $this->sanitize($payload)),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($line === false) {
            return;
        }

        file_put_contents($this->logDirectory . DIRECTORY_SEPARATOR . $fileName, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function sanitize(mixed $value): mixed
    {
        if ($value instanceof Throwable) {
            return [
                'message' => $value->getMessage(),
                'file' => $value->getFile(),
                'line' => $value->getLine(),
            ];
        }

        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->sanitize($item);
            }

            return $result;
        }

        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        return (string) $value;
    }
}
