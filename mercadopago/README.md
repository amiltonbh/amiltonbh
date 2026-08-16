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

**Opção 1 — arquivo de config (recomendado pra rodar via FTP no servidor):**

```bash
cp config.example.php config.php
# edite config.php e cole seu token na constante MP_ACCESS_TOKEN_INLINE
php list_devices.php
```

`config.php` está no `.gitignore` — fica só no servidor, nunca é commitado/pushado.
Esse repositório é **público**, então o token real nunca pode ir para o Git.

**Opção 2 — variável de ambiente:**

```bash
export MP_ACCESS_TOKEN="APP_USR-xxxxxxxxxxxxxxxx"
php list_devices.php
```

**Exportar também em CSV:**

```bash
php list_devices.php --csv=maquinas.csv
```
