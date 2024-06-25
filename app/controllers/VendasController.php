<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\AvaliacaoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\EtapaModel;
use App\Models\VendasModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use stdClass;

class VendasController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "vendas.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function listagem() {
        $vendasModel = new VendasModel();

        $result = $vendasModel->listagem();

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function get() {
        $vendasModel = new VendasModel();

        $result = $vendasModel->get($_POST['id']);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function save() {
        $vendasModel = new VendasModel();
        $vendasModel->save($_POST);

        $arrLista['success'] = true;

        echo json_encode($arrLista);
        return true;
    }    

    public function delete() {
        $vendasModel = new VendasModel();

        $result = $vendasModel->delete($_POST['id']);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function noPeriodo()  {
        $vendasModel = new VendasModel();

        $result = $vendasModel->noPeriodo($_POST['data']);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }
}