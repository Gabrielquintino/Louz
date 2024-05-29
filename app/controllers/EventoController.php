<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use App\Models\AtendimentoModel;
use App\Models\AvaliacaoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\EventoModel;
use App\Models\FuncionarioModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use stdClass;

class EventoController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "evento.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function listagem() {
        
        $eventoModel = new EventoModel();

        $result = $eventoModel->listagem();

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function get() {
        $eventoModel = new EventoModel();

        $result = $eventoModel->get($_POST['id']);

        $arrLista['success'] = true;
        $arrLista['data'] = $result[0];

        echo json_encode($arrLista);
        return true;
    }

    public function save() {
        $eventoModel = new EventoModel();
        $result = $eventoModel->save($_POST);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;        
    }

    public function delete() {

        $eventoModel = new EventoModel();

        $arrLista['success'] = $eventoModel->delete($_POST['id']);
        echo json_encode($arrLista);
        return true;
    }
}