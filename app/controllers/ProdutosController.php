<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\AvaliacaoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\EtapaModel;
use App\Models\ProdutosModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use stdClass;

class ProdutosController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "produtos.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function listagem() {
        $produtosModel = new ProdutosModel();

        $result = $produtosModel->listagem();

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function get() {
        
    }

    public function save() {
        
    }    

    public function delete() {
        
    }
}