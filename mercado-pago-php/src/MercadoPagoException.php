<?php

namespace MercadoPago;

class MercadoPagoException extends \RuntimeException
{
    /** @param array<string, mixed> $responseBody */
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly array $responseBody = []
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, mixed> */
    public function getResponseBody(): array
    {
        return $this->responseBody;
    }
}
