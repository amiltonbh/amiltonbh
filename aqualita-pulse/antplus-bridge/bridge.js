/*
 * Ponte ANT+ -> WebSocket para o Aqualita Pulse.
 *
 * O que faz: le o dongle ANT+ USB (ex.: RC406) usando a lib
 * "ant-plus-next" e retransmite a frequencia cardiaca de qualquer
 * pulseira ANT+ (ex.: HW807) para todos os navegadores conectados em
 * ws://localhost:8080, no formato que o painel espera:
 *   { "deviceId": "41123", "bpm": 142 }
 *
 * Por que isso existe: o navegador nao tem acesso direto a um dongle
 * ANT+ via USB, entao esse script roda localmente (no mesmo Mac do
 * painel) e faz a ponte entre o hardware e a pagina HTML.
 *
 * Uso:
 *   cd antplus-bridge
 *   npm install
 *   npm start
 *
 * A API exata da lib "ant-plus-next" pode variar entre versoes -
 * confira sempre os exemplos oficiais em
 * https://github.com/Benjamin-Stefan/ant-plus-next caso algo aqui
 * nao bata com a versao instalada.
 */
const { GarminStick2, GarminStick3, HeartRateSensor } = require('ant-plus-next');
const { WebSocketServer } = require('ws');

const WS_PORT = 8080;
const CHANNELS = 4; // quantas pulseiras ANT+ escutar ao mesmo tempo

const wss = new WebSocketServer({ port: WS_PORT });
console.log('[aqualita-pulse] ponte ANT+ ouvindo em ws://localhost:' + WS_PORT);

function broadcast(deviceId, bpm) {
  const payload = JSON.stringify({ deviceId: String(deviceId), bpm });
  wss.clients.forEach(client => {
    if (client.readyState === client.OPEN) client.send(payload);
  });
}

function onHeartRateData(data) {
  const bpm = data && (data.ComputedHeartRate || data.computedHeartRate);
  const deviceId = data && (data.DeviceID ?? data.deviceID ?? data.deviceId);
  if (deviceId != null && bpm) {
    console.log('[aqualita-pulse] FC recebida — device ' + deviceId + ': ' + bpm + ' bpm');
    broadcast(deviceId, bpm);
  }
}

async function openStick() {
  for (const StickClass of [GarminStick3, GarminStick2]) {
    const stick = new StickClass();
    try {
      const ok = await stick.open();
      if (ok) return stick;
    } catch (err) {
      // tenta o proximo modelo de dongle
    }
  }
  return null;
}

async function main() {
  const stick = await openStick();
  if (!stick) {
    console.error(
      '[aqualita-pulse] Nao foi possivel abrir o dongle ANT+. Verifique se o RC406 ' +
      'esta conectado na USB, se nenhum outro programa (Garmin Express, ANT Agent, etc.) ' +
      'esta usando a porta, e se as permissoes de USB do sistema foram concedidas.'
    );
    process.exit(1);
  }

  stick.on('startup', () => {
    console.log('[aqualita-pulse] dongle ANT+ conectado, abrindo canais de FC...');
    for (let channel = 0; channel < CHANNELS; channel++) {
      const sensor = new HeartRateSensor(stick);
      sensor.on('heartRateData', onHeartRateData);
      // canal 0, deviceId 0 = escuta qualquer pulseira ANT+ por perto (wildcard)
      sensor.attach(channel, 0);
    }
  });

  process.on('SIGINT', () => {
    console.log('\n[aqualita-pulse] encerrando...');
    try { stick.close(); } catch (_) {}
    process.exit(0);
  });
}

main();
