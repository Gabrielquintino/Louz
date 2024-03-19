<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use stdClass;
use DateTime;

class IntegracaoController
{

    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        $html = "integracao.html";
        include_once __DIR__ . '/../views/index.php';
    }

    public function listagem()
    {
        // Configurações da AWS
        $bucketName = 'wwebjs'; // Substitua pelo nome do seu bucket
        $prefix = 'sessions'; // Substitua pelo prefixo da pasta que deseja listar

        // Configuração do cliente S3
        $s3Client = new S3Client([
            'region'  => 'us-east-1', // Substitua pela região da sua S3
            'credentials' => [
                'key'    => AWS_ACCESS_KEY,
                'secret' => AWS_SECRET_ACCESS_KEY,
            ],
            'http' => [
                'verify' => false, // Desativar a verificação SSL
            ],
        ]);

        try {
            // Lista os objetos na pasta
            $objects = $s3Client->listObjects([
                'Bucket' => $bucketName,
                'Prefix' => $prefix
            ]);

            $arrLista = [
                'data' => []
            ];

            // Itera sobre os objetos e exibe o nome e a data de modificação de cada um
            foreach ($objects['Contents'] as $object) {
                if ($object['Key'] !== "sessions/") {
                    $objItem = new stdClass;
                    preg_match('/(\d+)/',$object['Key'], $matches);
                    $objItem->nome = $matches[0];
                    $data = new DateTime($object['LastModified']);
                    $objItem->data = $data->format('d/m/Y H:i:s');
                    array_push($arrLista['data'], $objItem);
                }
            }
        } catch (AwsException $e) {
            // Captura e exibe erros da AWS
            echo $e->getMessage() . "\n";
        }




        $arrLista['success'] = true;


        echo json_encode($arrLista);
        return true;
    }

    public function cadastrarWhatsapp()
    {

        $arrData = $_POST;

        $cloneModel = new UsuarioModel();
        $result = $cloneModel->save($arrData);

        return $result;
    }

    // Outras funções do controlador conforme necessário
}
