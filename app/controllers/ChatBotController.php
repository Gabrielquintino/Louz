<?php

namespace App\Controllers;

use App\Models\ChatBotModel;
use Exception;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class ChatBotController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "chatbot.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    /**
     * Lista os chatbots configurados
     * 
     * since 26/03/24
     * @author Gabriel Quintino <gabrielv.quintino@gmail.com>
     */
    public function listagem() {
        $chatBotModel = new ChatBotModel();

        $result = $chatBotModel->listagem();

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    /**
     * Salva a configuração do chatbot
     * 
     * since 26/03/24
     * @author Gabriel Quintino <gabrielv.quintino@gmail.com>
     */
    public function save() {
        $arrData = $_POST;
        $chatBotModel = new ChatBotModel();

        $result = $chatBotModel->save($arrData);

        if ($result['success']) {
            $arrLista['success'] = true;
            $arrLista['data'] = $result;
    
            echo json_encode($arrLista);
            return true;
        }

        return $result;
    }

    /**
     * Visualiza a configuração do chatbot
     * 
     * since 02/04/24
     * @author Gabriel Quintino <gabrielv.quintino@gmail.com>
     */
    public function getData() {
        $intId = $_POST['id'];
        $chatBotModel = new ChatBotModel();

        try {
            $result = $chatBotModel->getChatBot('id', $intId);
            $arrObj['success'] = true;
            $arrObj['data'] = $result;
    
            echo json_encode($arrObj);
            return true;
        } catch (Exception $th) {
            throw new Exception($th);
        }
    }

    public function delete() {
        $intId = $_POST['id'];
        $chatBotModel = new ChatBotModel();

        try {
            $result = $chatBotModel->delete($intId);
            $arrObj['success'] = true;
            $arrObj['data'] = $result;
    
            echo json_encode($arrObj);
            return true;
        } catch (Exception $th) {
            throw new Exception($th);
        }
    }

    public function upload() {
        // Tamanho máximo permitido para o upload (em bytes)
        $maxFileSize = 10 * 1024 * 1024; // 10 MB
    
        // Verifica se o arquivo foi enviado
        if (!isset($_FILES["file"])) {
            echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado']);
            return;
        }
    
        // Verifica se houve algum erro no upload
        if ($_FILES["file"]["error"] != UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo: ' . $_FILES["file"]["error"]]);
            return;
        }
    
        // Verifica o tamanho do arquivo
        if ($_FILES["file"]["size"] > $maxFileSize) {
            echo json_encode(['success' => false, 'message' => 'O arquivo excede o tamanho máximo permitido de 10MB']);
            return;
        }
    
        // Defina suas credenciais AWS
        $credentials = [
            'key'    => AWS_ACCESS_KEY,
            'secret' => AWS_SECRET_ACCESS_KEY,
        ];
    
        // Configure o cliente S3
        $s3 = new S3Client([
            'version'     => 'latest',
            'region'      => 'sa-east-1',
            'credentials' => $credentials,
            'http'        => [
                'verify' => false,
            ],
        ]);
    
        // Inicializa o objeto finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
    
        if (!$finfo) {
            echo json_encode(['success' => false, 'message' => 'Falha ao inicializar o finfo']);
            return;
        }
    
        // Obtém o tipo MIME do arquivo usando o caminho temporário do arquivo
        $mimeType = finfo_file($finfo, $_FILES["file"]["tmp_name"]);
    
        // Fecha o objeto finfo
        finfo_close($finfo);
    
        if (!$mimeType) {
            echo json_encode(['success' => false, 'message' => 'Não foi possível determinar o tipo MIME do arquivo']);
            return;
        }
    
        try {
            // Envia o arquivo para o Amazon S3 sem definir ACL
            $result = $s3->putObject([
                'Bucket' => 'iqon-files',
                'Key'    => $_SESSION['codigo_usuario'] . '/' . $_FILES["file"]["name"],
                'Body'   => fopen($_FILES["file"]["tmp_name"], 'r'),
                'ContentType' => $mimeType
            ]);
    
            $arrObj['success'] = true;
            $arrObj['link'] = $result['ObjectURL'];
    
            echo json_encode($arrObj);
        } catch (S3Exception $e) {
            echo "Erro ao enviar o arquivo para o Amazon S3: " . $e->getMessage();
            return false;
        }
    }
    
}