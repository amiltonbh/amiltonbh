<?php

namespace MercadoPago;

use MercadoPago\Support\Uuid;

/**
 * Checkout Transparente via API Orders (POST /v1/orders).
 * https://www.mercadopago.com.br/developers/pt/docs/checkout-api-orders/overview
 */
class OrdersService
{
    public function __construct(private readonly MercadoPagoClient $client)
    {
    }

    /**
     * Cria um pedido com pagamento em cartão de crédito/débito.
     *
     * O $cardToken é gerado no FRONTEND com o SDK JS do Mercado Pago
     * (Secure Fields / Card Payment Brick). O backend nunca deve receber
     * número de cartão, CVV ou validade em texto puro.
     *
     * @param array<string, mixed> $payer
     * @return array<string, mixed>
     */
    public function createCardOrder(
        string $amount,
        string $cardToken,
        string $paymentMethodId,
        int $installments,
        array $payer,
        string $externalReference,
        string $statementDescriptor = 'MINHA LOJA'
    ): array {
        $payload = [
            'type' => 'online',
            'total_amount' => $amount,
            'external_reference' => $externalReference,
            'processing_mode' => 'automatic',
            'payer' => $payer,
            'transactions' => [
                'payments' => [[
                    'amount' => $amount,
                    'payment_method' => [
                        'id' => $paymentMethodId,
                        'type' => 'credit_card',
                        'token' => $cardToken,
                        'installments' => $installments,
                        'statement_descriptor' => $statementDescriptor,
                    ],
                ]],
            ],
        ];

        return $this->client->post('/v1/orders', $payload, Uuid::v4());
    }

    /**
     * Cria um pedido com pagamento via Pix.
     *
     * @param array<string, mixed> $payer
     * @return array<string, mixed>
     */
    public function createPixOrder(
        string $amount,
        array $payer,
        string $externalReference,
        string $expirationTime = 'PT30M'
    ): array {
        $payload = [
            'type' => 'online',
            'total_amount' => $amount,
            'external_reference' => $externalReference,
            'processing_mode' => 'automatic',
            'payer' => $payer,
            'transactions' => [
                'payments' => [[
                    'amount' => $amount,
                    'payment_method' => [
                        'id' => 'pix',
                        'type' => 'bank_transfer',
                    ],
                    'expiration_time' => $expirationTime,
                ]],
            ],
        ];

        return $this->client->post('/v1/orders', $payload, Uuid::v4());
    }

    /** @return array<string, mixed> */
    public function getOrder(string $orderId): array
    {
        return $this->client->get('/v1/orders/' . urlencode($orderId));
    }
}
