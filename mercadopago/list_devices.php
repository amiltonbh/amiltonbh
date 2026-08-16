<?php
/**
 * Lista as maquininhas (Point) ativas na conta Mercado Pago e monta uma
 * tabela com o máximo de informação possível sobre cada uma, cruzando:
 *   - GET /point/integration-api/devices   (dispositivo físico)
 *   - GET /pos/{id}                        (caixa/POS vinculado ao device)
 *   - GET /stores/{store_id}                (loja vinculada ao POS)
 *
 * Uso:
 *   1) Copie config.example.php para config.php e cole seu token lá dentro, OU
 *   2) MP_ACCESS_TOKEN="APP_USR-xxxxx" php list_devices.php
 *
 *   php list_devices.php --csv=maquinas.csv
 *
 * O access token de PRODUÇÃO deve começar com "APP_USR-". O arquivo
 * config.php está no .gitignore e NUNCA deve ser commitado/pushado — este
 * repositório é público, então o token nunca pode ir para o Git.
 */

declare(strict_types=1);

const API_BASE = 'https://api.mercadopago.com';

$configFile = __DIR__ . '/config.php';
if (is_file($configFile)) {
    require $configFile;
}

function getAccessToken(): string
{
    $token = defined('MP_ACCESS_TOKEN_INLINE') ? MP_ACCESS_TOKEN_INLINE : getenv('MP_ACCESS_TOKEN');
    if (!$token) {
        fwrite(STDERR, "Erro: defina o token em mercadopago/config.php (veja config.example.php) ou na variável de ambiente MP_ACCESS_TOKEN.\n");
        exit(1);
    }
    if (!str_starts_with($token, 'APP_USR-')) {
        fwrite(STDERR, "Aviso: o token informado não parece ser um token de PRODUÇÃO (esperado prefixo APP_USR-).\n");
    }
    return $token;
}

/**
 * Faz uma chamada HTTP à API do Mercado Pago e devolve o corpo já decodificado.
 */
function mpRequest(string $method, string $path, string $accessToken, array $query = []): array
{
    $url = API_BASE . $path;
    if ($query) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
    ]);

    $body = curl_exec($ch);
    if ($body === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("Falha de conexão em {$path}: {$err}");
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($body, true) ?? [];

    if ($status >= 400) {
        $msg = $decoded['message'] ?? $body;
        throw new RuntimeException("Erro HTTP {$status} em {$path}: {$msg}");
    }

    return $decoded;
}

/**
 * Busca todos os devices (maquininhas) paginando o endpoint de Point.
 */
function fetchAllDevices(string $accessToken): array
{
    $devices = [];
    $offset  = 0;
    $limit   = 50;

    do {
        $resp = mpRequest('GET', '/point/integration-api/devices', $accessToken, [
            'offset' => $offset,
            'limit'  => $limit,
        ]);

        $batch = $resp['devices'] ?? [];
        $devices = array_merge($devices, $batch);

        $total  = $resp['paging']['total'] ?? count($devices);
        $offset += $limit;
    } while ($offset < $total);

    return $devices;
}

/** Busca detalhes de uma POS (caixa) pelo id. Retorna [] se não existir/erro. */
function fetchPos(string $accessToken, $posId): array
{
    if (!$posId) {
        return [];
    }
    try {
        return mpRequest('GET', "/pos/{$posId}", $accessToken);
    } catch (RuntimeException $e) {
        return [];
    }
}

/** Busca detalhes de uma loja pelo id. Retorna [] se não existir/erro. */
function fetchStore(string $accessToken, $storeId): array
{
    if (!$storeId) {
        return [];
    }
    try {
        return mpRequest('GET', "/stores/{$storeId}", $accessToken);
    } catch (RuntimeException $e) {
        return [];
    }
}

/**
 * Monta uma linha "achatada" com o máximo de informação disponível
 * sobre o device, cruzando com POS e Store.
 */
function buildRow(array $device, string $accessToken): array
{
    $posId   = $device['pos_id'] ?? null;
    $pos     = fetchPos($accessToken, $posId);
    $storeId = $pos['store_id'] ?? ($device['store_id'] ?? null);
    $store   = fetchStore($accessToken, $storeId);

    return [
        'device_id'       => $device['id'] ?? '',
        'operating_mode'  => $device['operating_mode'] ?? '',
        'pos_id'          => $posId ?? '',
        'pos_name'        => $pos['name'] ?? '',
        'external_pos_id' => $device['external_pos_id'] ?? ($pos['external_id'] ?? ''),
        'category'        => $pos['category'] ?? '',
        'fixed_amount'    => array_key_exists('fixed_amount', $pos) ? var_export($pos['fixed_amount'], true) : '',
        'store_id'        => $storeId ?? '',
        'store_name'      => $store['name'] ?? '',
        'store_ext_id'    => $store['external_id'] ?? '',
        'store_address'   => isset($store['location'])
            ? trim(($store['location']['address_line'] ?? '') . ' - ' . ($store['location']['city_name'] ?? ''))
            : '',
        'pos_date_created' => $pos['date_created'] ?? '',
    ];
}

/** Imprime uma tabela no terminal a partir de um array de linhas associativas. */
function printTable(array $rows): void
{
    if (!$rows) {
        echo "Nenhuma maquininha (Point) ativa foi encontrada nesta conta.\n";
        return;
    }

    $headers = array_keys($rows[0]);
    $widths  = [];
    foreach ($headers as $h) {
        $widths[$h] = mb_strlen($h);
    }
    foreach ($rows as $row) {
        foreach ($row as $k => $v) {
            $widths[$k] = max($widths[$k], mb_strlen((string) $v));
        }
    }

    $printLine = function (array $cols) use ($widths) {
        $parts = [];
        foreach ($cols as $k => $v) {
            $parts[] = str_pad((string) $v, $widths[$k]);
        }
        echo '| ' . implode(' | ', $parts) . " |\n";
    };

    $separator = '+-' . implode('-+-', array_map(fn ($h) => str_repeat('-', $widths[$h]), $headers)) . "-+\n";

    echo $separator;
    $printLine(array_combine($headers, $headers));
    echo $separator;
    foreach ($rows as $row) {
        $printLine($row);
    }
    echo $separator;
    echo count($rows) . " maquininha(s) encontrada(s).\n";
}

function writeCsv(array $rows, string $path): void
{
    $fh = fopen($path, 'w');
    if ($fh === false) {
        throw new RuntimeException("Não foi possível criar o arquivo {$path}");
    }
    if ($rows) {
        fputcsv($fh, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
    }
    fclose($fh);
    echo "CSV salvo em {$path}\n";
}

function main(array $argv): void
{
    $accessToken = getAccessToken();

    echo "Buscando maquininhas ativas na conta...\n";
    $devices = fetchAllDevices($accessToken);

    $rows = [];
    foreach ($devices as $device) {
        $rows[] = buildRow($device, $accessToken);
    }

    printTable($rows);

    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--csv=')) {
            writeCsv($rows, substr($arg, strlen('--csv=')));
        }
    }
}

main($argv);
