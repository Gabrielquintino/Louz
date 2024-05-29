<?php

namespace App\Controllers;

use App\Models\CargoModel;
use Exception;
use stdClass;

class CargoController
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
        
        $cargoModel = new CargoModel();

        $result = $cargoModel->listagem(true);

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function delete() {
        $cargoModel = new CargoModel();
        $arrData['success'] = true;
        $arrData['data'] = $cargoModel->delete($_POST['id'], $_POST['newId']);

        echo json_encode($arrData);
        return true;
    }
}
