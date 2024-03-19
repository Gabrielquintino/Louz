const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode-terminal');
const app = express();
const port = 3000;
const { Client, RemoteAuth } = require('whatsapp-web.js');
const { AwsS3Store } = require('wwebjs-aws-s3');
const {
  S3Client,
  PutObjectCommand,
  HeadObjectCommand,
  GetObjectCommand,
  DeleteObjectCommand
} = require('@aws-sdk/client-s3');

const s3 = new S3Client({
  region: 'us-east-1',
  credentials: {
    accessKeyId: 'AKIA47CR26CB3JJ3Y3WM',
    secretAccessKey: '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'
  }
});

const putObjectCommand = PutObjectCommand;
const headObjectCommand = HeadObjectCommand;
const getObjectCommand = GetObjectCommand;
const deleteObjectCommand = DeleteObjectCommand;

const store = new AwsS3Store({
  bucketName: 'wwebjs',
  remoteDataPath: 'sessions/',
  s3Client: s3,
  putObjectCommand,
  headObjectCommand,
  getObjectCommand,
  deleteObjectCommand
});

// Adicione o middleware cors
app.use(cors());

app.get('/qrcode', (req, res) => {
  res.writeHead(200, { 'Content-Type': 'application/json' });

  const { clientId } = req.query;

  const client = new Client({
    authStrategy: new RemoteAuth({
      clientId: clientId,
      dataPath: 'sessions',
      store: store,
      backupSyncIntervalMs: 600000
    })
  });

  client.initialize();

  client.on('message', async (message) => {
    if (message.body === 'oi') {
      await message.reply('oi tudo bem? como posso ajudar você hoje?');
    }
  });

  client.on('qr', (qr) => {
    res.write(JSON.stringify({ type: 'qr', data: qr, clientId: clientId }));
    res.end(); // Finaliza a resposta após enviar o QR code
    console.log('QR code gerado para ' + clientId);
  });

  app.get('/conectado', async (req, res) => {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    client.on('ready', () => {
      console.log('Client is ready!');
      res.write(JSON.stringify({ type: 'ready' }))
      res.end();
    });
  
  });
});

// Definir a rota '/conectado'


app.listen(port, () => {
  console.log(`Servidor rodando em http://3.81.81.196:${port}`);
});
