<?php

require __DIR__ . '/../autoload.php';

use MercadoPago\MercadoPagoClient;
use MercadoPago\MercadoPagoException;
use MercadoPago\OrdersService;
use MercadoPago\Support\Env;

Env::load(__DIR__ . '/../.env');

$accessToken = getenv('MP_ACCESS_TOKEN') ?: '';

$payer = [
    'email' => 'comprador@example.com',
    'entity_type' => 'individual',
    'first_name' => 'João',
    'last_name' => 'Silva',
    'identification' => [
        'type' => 'CPF',
        'number' => '19119119100',
    ],
];

try {
    $client = new MercadoPagoClient($accessToken);
    $orders = new OrdersService($client);

    $order = $orders->createPixOrder(
        amount: '150.00',
        payer: $payer,
        externalReference: 'pedido-' . uniqid(),
        expirationTime: 'PT30M'
    );

    echo "Pedido Pix criado:\n";
    echo json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

    // O QR Code/copia-e-cola normalmente vem em
    // $order['transactions']['payments'][0]['payment_method']['qr_code']
    // e ['qr_code_base64'] — confira a resposta completa acima.
} catch (MercadoPagoException $e) {
    fwrite(STDERR, "Erro Mercado Pago ({$e->getStatusCode()}): {$e->getMessage()}\n");
    fwrite(STDERR, json_encode($e->getResponseBody(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}
