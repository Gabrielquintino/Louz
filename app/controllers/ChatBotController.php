<?php

namespace App\Controllers;

use App\Models\ChatBotModel;
use Exception;

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
            include_once __DIR__ . '/../views/index.php';
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
            $result = $chatBotModel->getChatBot($intId);
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

}