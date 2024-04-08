<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class RegistrarController {


    /**
     * Retorna o arquivo
     */
    public function index() {
        // Lógica de roteamento e controle aqui
        include_once __DIR__ . '/../views/cadastrar.html';
    }

    public function cadastrar() {

        $arrData = $_POST;

        $cloneModel = new UsuarioModel();
        $result = $cloneModel->save($arrData);

        echo json_encode(['success' => true]);
        return $result;

    }
    
    // Outras funções do controlador conforme necessário
}

