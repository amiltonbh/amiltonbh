<?php

namespace MercadoPago;

/**
 * Consulta de saldo da conta Mercado Pago.
 * GET /users/{user_id}/mercadopago_account/balance
 */
class BalanceService
{
    public function __construct(private readonly MercadoPagoClient $client)
    {
    }

    /** @return array<string, mixed> */
    public function getBalance(string $userId): array
    {
        return $this->client->get('/users/' . urlencode($userId) . '/mercadopago_account/balance');
    }
}
