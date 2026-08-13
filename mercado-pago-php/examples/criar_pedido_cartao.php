<?php

require __DIR__ . '/../autoload.php';

use MercadoPago\MercadoPagoClient;
use MercadoPago\MercadoPagoException;
use MercadoPago\OrdersService;
use MercadoPago\Support\Env;

Env::load(__DIR__ . '/../.env');

$accessToken = getenv('MP_ACCESS_TOKEN') ?: '';

// O card_token é gerado no FRONTEND com o SDK JS do Mercado Pago (Secure
// Fields / Card Payment Brick). Nunca trafegue número de cartão, CVV ou
// validade pelo seu backend.
$cardToken = 'TOKEN_GERADO_NO_FRONTEND';
$paymentMethodId = 'visa'; // retornado junto com o token pelo SDK JS

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

    $order = $orders->createCardOrder(
        amount: '150.00',
        cardToken: $cardToken,
        paymentMethodId: $paymentMethodId,
        installments: 1,
        payer: $payer,
        externalReference: 'pedido-' . uniqid(),
        statementDescriptor: 'MINHA LOJA'
    );

    echo "Pedido criado:\n";
    echo json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (MercadoPagoException $e) {
    fwrite(STDERR, "Erro Mercado Pago ({$e->getStatusCode()}): {$e->getMessage()}\n");
    fwrite(STDERR, json_encode($e->getResponseBody(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
}
