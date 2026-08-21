# Ponte ANT+ do Aqualita Pulse

Script local em Node.js que lê o dongle ANT+ USB (RC406) e retransmite a
frequência cardíaca das pulseiras ANT+ (HW807) para o painel `index.html`
via WebSocket, já que o navegador não acessa USB/ANT+ diretamente.

A HW702A (BLE ANT+) **não precisa** dessa ponte — ela é pareada direto pelo
navegador em ⚙ → "Parear via Bluetooth".

## Requisitos (macOS)

- Node.js 18+
- Xcode Command Line Tools (`xcode-select --install`) — necessário para
  compilar o binding nativo de acesso USB usado pela lib `ant-plus-next`.
- Dongle RC406 conectado numa porta USB e nenhum outro app (Garmin Express,
  ANT Agent, etc.) usando-o ao mesmo tempo.

## Uso

```bash
cd antplus-bridge
npm install
npm start
```

Se abrir com sucesso, o terminal mostra:

```
[aqualita-pulse] ponte ANT+ ouvindo em ws://localhost:8080
[aqualita-pulse] dongle ANT+ conectado, abrindo canais de FC...
```

Com o `index.html` do painel aberto no navegador (mesma máquina), ele já
tenta conectar em `ws://localhost:8080` automaticamente. Ligue as pulseiras
HW807 e ative-as no pulso — o painel detecta o `deviceId` sozinho (auto-
cadastro) ou você pode pré-cadastrar o ID impresso na pulseira em ⚙ →
"Cadastro manual".

## Se der erro ao instalar/abrir

- A API exata da lib `ant-plus-next` pode mudar entre versões — confira os
  exemplos oficiais em https://github.com/Benjamin-Stefan/ant-plus-next se
  `bridge.js` não bater com a versão instalada.
- Erro ao compilar o binding nativo do USB: instale as Command Line Tools
  do Xcode e rode `npm install` de novo.
- "Nao foi possivel abrir o dongle ANT+": feche outros programas que usam
  ANT+/USB e confirme que o RC406 está bem encaixado.
