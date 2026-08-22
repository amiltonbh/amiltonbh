# Aqualita Pulse

Painel gamificado de frequência cardíaca em tempo real, com cadastro de
dispositivos direto na tela.

## Equipamentos suportados

- **HW702A** (BLE ANT+, 3 unidades) — pareada direto pelo navegador via
  Web Bluetooth. Não precisa de nenhum programa extra.
- **HW807** (ANT+ puro, 2 unidades) + **RC406** (dongle ANT+ USB) — o
  navegador não acessa USB/ANT+ diretamente, então usam a ponte local em
  [`antplus-bridge/`](antplus-bridge/).
- **Anéis inteligentes tipo R99** — também respondem ao Serviço padrão de
  FC (mesmo botão da HW702A), mas só com leituras periódicas. Para o
  streaming contínuo (o "modo corrida" do app do anel), use o botão
  "💍 Parear anel em tempo real": ele fala com o serviço proprietário do
  anel (engenharia reversa da família Colmi R02/R06/R09/R10, chip/SDK
  compartilhado por boa parte dos anéis baratos, incluindo clones vendidos
  como R99). Se o firmware do seu anel específico não bater com esse
  protocolo, o pareamento padrão continua funcionando em modo periódico.

## Como usar

1. Abra `index.html` num navegador (Chrome ou Edge recomendados no Mac,
   por causa do Web Bluetooth).
2. Clique no ⚙ no canto superior direito do cabeçalho.
3. Para uma **HW702A**: preencha nome/idade e clique em "Parear via
   Bluetooth" — escolha a pulseira na lista do sistema.
4. Para uma **HW807**: cadastre nome, idade e o ID ANT+ do dispositivo
   (manual), depois rode a ponte:
   ```bash
   cd antplus-bridge
   npm install
   npm start
   ```
5. Conforme cada pulseira conecta, o card dela aparece sozinho na tela e
   o grid se reorganiza automaticamente — com menos gente conectada, os
   avatares ficam maiores; conforme mais pulseiras entram, o grid se
   ajusta para caber todo mundo.
6. Qualquer dispositivo desconhecido que enviar sinal também aparece
   sozinho no painel (auto-cadastro), pronto para você renomear em ⚙.

Os cadastros ficam salvos no `localStorage` do navegador, então continuam
lá na próxima aula.
