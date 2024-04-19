<?php

namespace App\Controllers;

use App\Models\ChatBotModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\UsuarioModel;
use stdClass;

class InstanciaWppController {

    public function receiveWebhook($objData) {
        echo "<pre>";
        // Obtém a URL atual
        $url = $_SERVER['REQUEST_URI'];

        #region Dados necessarios
        
        // Encontra a posição de "/instancia/" na URL
        $posicaoInstancia = strpos($url, '/instancia/') + strlen('/instancia/');
        
        // Extrai o valor da instância da URL
        $instancia = substr($url, $posicaoInstancia, strpos($url, '/', $posicaoInstancia) - $posicaoInstancia);

        // Obtem dados da instancia
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();
        $arrInstancia = $objUsuarioInstanciaModel->getInstance(pStrInstance: $instancia);
        if (empty($arrInstancia)) {
            echo "Instancia nao encontrada";
            return false;
        }

        // Obtem dados do Usuario
        $objUsuario = new UsuarioModel();
        $arrUsuario = $objUsuario->obterUsuarioPorId($arrInstancia[0]['usuario_id']);
        if (empty($arrUsuario)) {
            echo "Instancia nao configurada";
            return false;
        }

        $objUser = $arrUsuario['data'][0];
        $strUserCode = $objUser['codigo'];
        $strDbUser = 'db_' . $strUserCode;

        // Obtem dados do ChatBot
        $chatBotModel = new ChatBotModel();
        $objChatBot = $chatBotModel->getChatBot($objData->connectedPhone, $strDbUser);
        if (empty($objChatBot)) {
            echo "Chatbot nao configurado para a instancia";
            return false;
        }
        $objChatBotJson = json_decode($objChatBot['json']);
        $arrChatBotOrder = json_decode($objChatBot['arr_ordem']);
        $arrFluxo = $objChatBotJson->cells;
        $arrListMessage = [];

        #endregion

    

        foreach ($arrChatBotOrder as $key => $objChat) {
            if ($objChat->type != 'standard.Link') {
                if ($objChat->type == 'standard.HeaderedRectangle') {
                    if (property_exists($objChat->attrs->bodyText, 'description')) {
                        array_push($arrListMessage, [
                            'index' => $key,
                            'text' => $objChat->attrs->bodyText->description,
                            'ports' => $objChat->ports->items,
                            'id' => $objChat->id
                        ]);
                    }
                } else {
                    array_push($arrListMessage, [
                        'index' => $key,
                        'type' => $objChat->type,
                        'ports' => $objChat->ports->items,
                        'id' => $objChat->id
                    ]);
                }
            }
        }

        // print_r($arrListMessage);
        // echo "///////arrChatBotOrder////////////";
        print_r($arrChatBotOrder);




        // Mensagem de texto
        if (property_exists($objData, 'text')) {
            $strMsg = $objData->text->message;
            echo $strMsg;
        }
        // Lista de botao
        if (property_exists($objData, 'buttonsResponseMessage')) {
            # code...
        }
        // Lista de opçao
        if (property_exists($objData, 'listResponseMessage')) {
            # code...
        }

        var_dump($objData);
    }
}