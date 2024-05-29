<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use App\Models\AtendimentoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class AgendamentoController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "agendamento.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function listagem() 
    {
        $agendamentoModel = new AgendamentoModel();

        $arrList['success'] = true;
        $arrList['data'] = $agendamentoModel->listagem();

        echo json_encode($arrList);
        return true;
    }

    public function get() {
        $agendamentoModel = new AgendamentoModel();
        $arrData['success'] = true;
        $arrData['data'] = $agendamentoModel->get($_POST['id']);

        echo json_encode($arrData);
        return true;
    }

    public function delete() {
        $agendamentoModel = new AgendamentoModel();
        $arrData['success'] = true;
        $arrData['data'] = $agendamentoModel->delete($_POST['id']);

        echo json_encode($arrData);
        return true;
    }
}