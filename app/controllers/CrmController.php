<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\AvaliacaoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\EtapaModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use stdClass;

class CrmController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "crm.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function kanbam() {
        $etapaModel = new EtapaModel();
        $arrEtapas = $etapaModel->list();

        $arrLog = $etapaModel->log();

        $arrList['success'] = true;
        $arrList['etapas'] = $arrEtapas;
        $arrList['log'] = $arrLog;


        echo json_encode($arrList);
        return true;
    }

    public function saveEtapa() {
        $etapaModel = new EtapaModel();
        $etapaModel->save($_POST);
        
        $arrList['success'] = true;
        echo json_encode($arrList);
        return true;
    }

    public function saveKanbam() {
        try {
            $etapaModel = new EtapaModel();
            $etapaModel->saveLog($_POST['clientId'], $_POST['etapaId']);
            $arrList['success'] = true;
            echo json_encode($arrList);
            return true;
        } catch (Exception $err) {
            throw new Exception($err->getMessage(), 1);
            
        }
    }

    public function listagem()
    {
        $clientModel = new ClienteModel();

        $arrList['success'] = true;
        $arrList['data'] = $clientModel->list();

        echo json_encode($arrList);
        return true;
    }

    public function save()
    {
        $clientModel = new ClienteModel();

        $arrData = [];

        $clientModel->save($_POST);

        $arrList['success'] = true;
    }

    public function edit()
    {
        $clientModel = new ClienteModel();
        $atendimentoModel = new AtendimentoModel();

        $arrList['success'] = true;
        $arrList['data'] = $clientModel->getClient('id', $_POST['id'])[0];
        $arrList['data']['obs'] = $atendimentoModel->getLog('cliente_id', $_POST['id']);

        echo json_encode($arrList);
        return true;
    }

    public function delete()
    {
        $clientModel = new ClienteModel();
        $clientModel->delete($_POST['id']);
        $arrList['success'] = true;

        echo json_encode($arrList);
        return true;
    }

    public function deleteEtapa()
    {
        $etapaModel = new EtapaModel();
        try {
            $etapaModel->delete($_POST['etapaId'], $_POST['newEtapaId'], $_POST['booTemClinte']);

            $arrList['success'] = true;
    
            echo json_encode($arrList);
            return true;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function history()
    {
        $arrList['success'] = true;
        $arrChat = [];

        $objAvalicao = new AvaliacaoModel();

        $arrData = $objAvalicao->listClientFeedbacks($_POST['id']);

        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();
        $arrInstancia = $objUsuarioInstanciaModel->getInstanceByUser($_SESSION['user_id']);

        $instanciaWpp = new WhatsappApiModel();
        $objChat = $instanciaWpp->getChatById($arrInstancia[0]["token"], $_POST['telefone']);

        $strContactPhoto = "";
        $strClientPhoto = "";


        if (!empty($objChat) && property_exists($objChat, 'messages')) {
            $strContactPhoto = $objChat->contactPhoto;
            $strClientPhoto = $objChat->clientPhoto;
            foreach ($objChat->messages as $key => $message) {
                $objMessage = new stdClass();
                if ($message->type == 'chat') {
                    $objMessage->message = $message->body;
                }
                if ($message->type == 'ptt') {
                    $objMessage->message = 'AUDIO';
                }
                if ($message->type == 'video') {
                    $objMessage->message = 'VIDEO';
                }
                if ($message->type == 'image') {
                    $objMessage->message = 'FOTO';
                }
                $objMessage->fromMe = $message->fromMe;
                $objMessage->number = $message->_data->from->user;
                $objMessage->data =  date('d/m/y H:i', $message->timestamp);

                array_push($arrChat, $objMessage);
            }
        }
        
        $arrList['data'] = [
            'avaliacoes' => $arrData,
            'chat' => $arrChat,
            'contactPhoto' => $strContactPhoto,
            'clientPhoto' => $strClientPhoto
        ];

        echo json_encode($arrList);

        // Não retorne nada aqui, pois a resposta será enviada dentro da função de retorno do getChatById

    }
}
