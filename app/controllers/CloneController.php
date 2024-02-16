<?php

namespace App\Controllers;

use App\Models\CloneModel;

class CloneController {

    /**
     * Retorna o arquivo
     */
    public function index() {
        // Lógica de roteamento e controle aqui
        include_once __DIR__ . '/../views/index.php';
    }

    public function importSite() {

        $arrData = $_POST;

        $cloneModel = new CloneModel();
        $result = $cloneModel->save($arrData);

        return $result;
    }
    
    // Outras funções do controlador conforme necessário
}

