<?php

namespace MercadoPago;

class MercadoPagoClient
{
    private const BASE_URL = 'https://api.mercadopago.com';

    public function __construct(private readonly string $accessToken)
    {
        if ($this->accessToken === '') {
            throw new \InvalidArgumentException('Access token do Mercado Pago não informado.');
        }
    }

    /** @return array<string, mixed> */
    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function post(string $path, array $body, ?string $idempotencyKey = null): array
    {
        $headers = [];
        if ($idempotencyKey !== null) {
            $headers[] = 'X-Idempotency-Key: ' . $idempotencyKey;
        }

        return $this->request('POST', $path, $body, $headers);
    }

    /**
     * @param array<string, mixed>|null $body
     * @param string[] $extraHeaders
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body = null, array $extraHeaders = []): array
    {
        $ch = curl_init(self::BASE_URL . $path);

        $headers = array_merge([
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ], $extraHeaders);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("Falha na requisição cURL: {$error}");
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        $decoded = is_array($decoded) ? $decoded : [];

        if ($statusCode >= 400) {
            $message = $decoded['message'] ?? "Erro HTTP {$statusCode} ao chamar {$method} {$path}";
            throw new MercadoPagoException($message, $statusCode, $decoded);
        }

        return $decoded;
    }
}
