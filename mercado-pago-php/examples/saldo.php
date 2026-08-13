<?php

require __DIR__ . '/../autoload.php';

use MercadoPago\BalanceService;
use MercadoPago\MercadoPagoClient;
use MercadoPago\MercadoPagoException;
use MercadoPago\Support\Env;

Env::load(__DIR__ . '/../.env');

$accessToken = getenv('MP_ACCESS_TOKEN') ?: '';
$userId = getenv('MP_USER_ID') ?: '';

try {
    $client = new MercadoPagoClient($accessToken);
    $balanceService = new BalanceService($client);
    $balance = $balanceService->getBalance($userId);

    echo "Saldo da conta Mercado Pago\n";
    echo "----------------------------\n";
    echo json_encode($balance, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (MercadoPagoException $e) {
    fwrite(STDERR, "Erro Mercado Pago ({$e->getStatusCode()}): {$e->getMessage()}\n");
    fwrite(STDERR, json_encode($e->getResponseBody(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}
