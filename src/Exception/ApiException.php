<?php

namespace App\Exception;

class ApiException extends \RuntimeException
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        private readonly string $errorCode,
        string $message,
        private readonly int $statusCode = 400,
        private readonly array $details = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    /**
     * @param array<string, mixed> $details
     */
    public static function badRequest(string $errorCode, string $message, array $details = []): self
    {
        return new self($errorCode, $message, 400, $details);
    }

    /**
     * @param array<string, mixed> $details
     */
    public static function notFound(string $errorCode, string $message, array $details = []): self
    {
        return new self($errorCode, $message, 404, $details);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
