# Mercado Pago API — estudos

Scripts PHP de estudo/teste da API do Mercado Pago (produção).

## `list_devices.php`

Lista as maquininhas (Point) ativas na conta e monta uma tabela cruzando:

- `GET /point/integration-api/devices` — dispositivo físico (id, modo de operação)
- `GET /pos/{id}` — caixa (POS) vinculado ao dispositivo
- `GET /stores/{store_id}` — loja vinculada à POS

### Requisitos

- PHP 8+ com extensão `curl`
- Access token de **produção** (prefixo `APP_USR-`) com permissão de leitura de Point/POS/Stores

### Uso

```bash
export MP_ACCESS_TOKEN="APP_USR-xxxxxxxxxxxxxxxx"
php list_devices.php

# ou exportando também para CSV
php list_devices.php --csv=maquinas.csv
```

O token nunca deve ser commitado — é lido sempre da variável de ambiente `MP_ACCESS_TOKEN`.
