<?php

require __DIR__ . '/../autoload.php';

use MercadoPago\MercadoPagoClient;
use MercadoPago\MercadoPagoException;
use MercadoPago\OrdersService;
use MercadoPago\Support\Env;

Env::load(__DIR__ . '/../.env');

$orderId = $argv[1] ?? null;

if ($orderId === null) {
    fwrite(STDERR, "Uso: php consultar_pedido.php <order_id>\n");
    exit(1);
}

$accessToken = getenv('MP_ACCESS_TOKEN') ?: '';

try {
    $client = new MercadoPagoClient($accessToken);
    $orders = new OrdersService($client);
    $order = $orders->getOrder($orderId);

    echo json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (MercadoPagoException $e) {
    fwrite(STDERR, "Erro Mercado Pago ({$e->getStatusCode()}): {$e->getMessage()}\n");
    exit(1);
}
