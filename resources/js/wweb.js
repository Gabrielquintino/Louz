const express = require('express');
const cors = require('cors');
const app = express();
const port = 3000;

// Adicione o middleware cors
app.use(cors());

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


let client; // Defina a variável client fora do escopo das rotas

app.get("/qrcode", (req, res) => {
    res.writeHead(200, { 'Content-Type': 'application/json' });

    console.log(req.query.clientId)

    const clientId = req.query.clientId

    const store = new AwsS3Store({
        bucketName: 'wwebjs',
        remoteDataPath: 'sessions/' + clientId,
        s3Client: s3,
        putObjectCommand,
        headObjectCommand,
        getObjectCommand,
        deleteObjectCommand
    });


    try {

        const { Client, RemoteAuth } = require('whatsapp-web.js');
        const { AwsS3Store } = require('wwebjs-aws-s3');

        const client = new Client({
            authStrategy: new RemoteAuth({
                clientId: clientId,
                dataPath: clientId,
                store: store,
                backupSyncIntervalMs: 600000
            })
        });


        client.initialize().catch(error => {
            console.error('Erro ao inicializar o cliente:', error);
        });

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
    } catch (error) {
        console.error('Erro ao criar o cliente:', error);
    }
});

    // Definir a rota '/conectado' fora do callback do endpoint '/qrcode'
app.get('/conectado', async (req, res) => {
    res.writeHead(200, { 'Content-Type': 'application/json' });

    // Verifica se o cliente está definido e conectado
    if (client) {

        client.on('ready', (data) => {
            res.write(JSON.stringify({ type: 'connected' }));
        })
    } else {
        res.write(JSON.stringify({ type: 'not connected' }));
    }

    res.end();
});



app.listen(port, () => {
    console.log(`Servidor rodando em http://3.81.81.196:${port}`);
});
