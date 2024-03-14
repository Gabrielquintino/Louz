const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode-terminal');
const app = express();
const port = 3000;
const { Client, LocalAuth } = require('whatsapp-web.js');

// Adicione o middleware cors
app.use(cors());

app.get('/qrcode', (req, res) => {
  res.writeHead(200, { 'Content-Type': 'application/json' });

  const { clientId } = req.query;

  const client1 = new Client({
    authStrategy: new LocalAuth({ clientId: clientId })
  });

  client1.initialize();

  client1.on('message', async (message) => {
    if (message.body === 'oi') {
      await message.reply('oi tudo bem? como posso ajudar você hoje?');
    }
  });

  client1.on('qr', (qr) => {
    res.write(JSON.stringify({ type: 'qr', data: qr, clientId: clientId }));
    res.end(); // Finaliza a resposta após enviar o QR code
    console.log('QR code gerado para ' + clientId);
  });



});

app.get('/conectado', (req, res) => {
  res.writeHead(200, { 'Content-Type': 'application/json' });
  Client.on('ready', () => {
    res.write(JSON.stringify({ type: 'ready' }));
    res.end(); // Finaliza a resposta após enviar a prontidão
    console.log('Client is ready!');
  });
});

app.listen(port, () => {
  console.log(`Servidor rodando em http://localhost:${port}`);
});
