# Integração PHP — Mercado Pago (Checkout Transparente / API Orders)

Cliente PHP puro (sem dependências, só `curl` e `json`) para:

- Criar pedidos via **API Orders** (`POST /v1/orders`) com cartão ou Pix — Checkout Transparente.
- Consultar um pedido (`GET /v1/orders/{id}`).
- Consultar o **saldo da conta** (`GET /users/{user_id}/mercadopago_account/balance`).

## Requisitos

- PHP 8.1+
- Extensão `curl` habilitada

## Configuração

1. Copie `.env.example` para `.env` e preencha com suas credenciais:

   ```
   cp .env.example .env
   ```

   ```
   MP_ACCESS_TOKEN=seu_access_token_de_producao_ou_teste
   MP_USER_ID=seu_user_id
   ```

2. **Nunca** commite o arquivo `.env` (já está no `.gitignore`) nem coloque o
   Access Token direto no código. Ele dá acesso total à conta Mercado Pago.
   Se um token de produção já foi exposto (ex.: colado em chat, print, etc.),
   gere um novo em *Suas integrações → Credenciais* no painel do Mercado Pago
   e revogue o antigo.

## Uso

```bash
# Consultar saldo
php examples/saldo.php

# Criar pedido com cartão (troque o card_token pelo gerado no frontend)
php examples/criar_pedido_cartao.php

# Criar pedido com Pix
php examples/criar_pedido_pix.php

# Consultar um pedido existente
php examples/consultar_pedido.php ORD01J49MMW3SSBK5PSV3DFR32959
```

## Estrutura

```
src/
  MercadoPagoClient.php   # cliente HTTP (Bearer token, idempotency key, erros)
  OrdersService.php       # criação/consulta de pedidos (Checkout Transparente)
  BalanceService.php      # consulta de saldo
  Support/Uuid.php        # gera X-Idempotency-Key
  Support/Env.php         # carregador simples de .env
examples/                 # scripts de exemplo prontos para rodar
```

## Sobre o Checkout Transparente com cartão

O número do cartão, CVV e validade **nunca** devem passar pelo seu backend.
No frontend, use o SDK JS do Mercado Pago (Secure Fields ou o *Card Payment
Brick*) para gerar um `card_token` a partir dos dados digitados pelo cliente.
Esse token (e o `payment_method_id`, ex. `visa`) é o que você envia para
`OrdersService::createCardOrder()`.

## Observação sobre o endpoint de saldo

O endpoint de saldo (`/users/{user_id}/mercadopago_account/balance`) é o
usado historicamente para consulta em tempo real do saldo da conta. Os nomes
exatos dos campos retornados podem variar; confira sempre a resposta bruta
(o exemplo já imprime o JSON completo) e a documentação oficial mais
recente em https://www.mercadopago.com.br/developers antes de usar em
produção.
