<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\AvaliacaoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\FuncionarioModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use stdClass;

class FuncionarioController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "funcionario.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function listagem() {
        $funcionarioModel = new FuncionarioModel();

        $booOnlyActive = isset($_POST['onlyActive']) ? (bool) $_POST['onlyActive'] : false;

        $result = $funcionarioModel->listagem($booOnlyActive);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function get() {
        $funcionarioModel = new FuncionarioModel();

        $result = $funcionarioModel->get($_POST['id']);

        $arrLista['success'] = true;
        $arrLista['data'] = $result[0];

        echo json_encode($arrLista);
        return true;        
    }

    public function save() {
        $funcionarioModel = new FuncionarioModel();
        $result = $funcionarioModel->save($_POST);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;        
    }

    public function delete() {

        $funcionarioModel = new FuncionarioModel();

        $arrLista['success'] = $funcionarioModel->delete($_POST['id']);
        echo json_encode($arrLista);
        return true;
    }    
}