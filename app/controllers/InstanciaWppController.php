<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\UsuarioModel;
use App\Models\ZapiModel;
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
        $arrChatBot = $chatBotModel->getChatBot('integration_phone', $objData->connectedPhone);
        if (empty($arrChatBot)) {
            echo "Chatbot nao configurado para a instancia";
            return false;
        }
        $objChatBotJson = json_decode($arrChatBot['json']);
        $arrChatBotOrder = json_decode($arrChatBot['arr_ordem']);
        $arrFluxo = $objChatBotJson->cells;
        $arrListMessage = [];

        #endregion

        // Obtém dados do cliente
        $clienteModel = new ClienteModel();
        $arrCliente = $clienteModel->getClient('telefone', $objData->phone);

        if (empty($arrCliente)) {
            // Cliente novo
            $intClienteId = $clienteModel->save([
                'nome' => $objData->senderName,
                'telefone' => $objData->phone
            ]);
        } else {
            $intClienteId = $arrCliente[0]['id'];
        }

        $atentimentoModel = new AtendimentoModel();
        $arrAtendimento = $atentimentoModel->getAtendimento('cliente_id', $intClienteId);

        $intLengthAtendimento = 0;

        foreach ($arrChatBotOrder as $key => $objChat) {
            if ($objChat->type != 'standard.Link') {
                if ($objChat->type == 'standard.HeaderedRectangle') {
                    $intLengthAtendimento++;
                    $strAnterior = '';
                    $strTextMsg =  '';
                    if (property_exists($objChat->attrs->bodyText, 'anterior')) {
                        $strAnterior = $objChat->attrs->bodyText->anterior;
                    }
                    if (property_exists($objChat->attrs->bodyText, 'description')) {
                        $strTextMsg = $objChat->attrs->bodyText->description;
                    }

                    $arrMessage = [
                        'index' => $key,
                        'type' => $objChat->attrs->headerText->text,
                        'text' => $strTextMsg,
                        'id' => $objChat->id,
                        'option_selected' => $strAnterior
                    ];

                    array_push($arrListMessage, $arrMessage);

                } elseif ($objChat->type == 'standard.Polygon') {
                    $arrOptions = [
                        'type' => 'options',
                        'index' => $key,
                        'options' => []
                    ];

                    foreach ($objChat->ports->items as $key => $value) {
                        if ($value->group == 'out') {
                            array_push($arrOptions['options'], $value->attrs->label->text);
                        }
                    }

                    array_push($arrListMessage, $arrOptions);
                }
            }
        }

        // print_r($arrListMessage);
        // echo "///////arrChatBotOrder////////////";
        // print_r($arrListMessage);

        // Mensagem de texto
        if (property_exists($objData, 'text')) {
            $strMsg = $objData->text->message;

            if (empty($arrAtendimento)) {
                $arrAtendimento = [
                    'chatbot_id' => $arrChatBot['id'],
                    'cliente_id' => $intClienteId,
                    'mensagem' => mb_substr($strMsg, 0, 200),
                    'index' => 0,
                    'status' => 'andamento'
                ];
                $atentimentoModel->save($arrAtendimento);
            }

            $strMsgSended = '';

            if ($arrListMessage[$arrAtendimento['index']]['type'] == 'Pergunta') {
                
                $strMsgSended = $arrListMessage[$arrAtendimento['index']]['text'];
                $strMsgSended .= ". Responda com " . implode(" ou ", $arrOptions['options']);

            } elseif ($arrListMessage[$arrAtendimento['index']]['type'] == 'options') {

                // Respondeu de acordo com as opções
                if (in_array($strMsg, $arrListMessage[$arrAtendimento['index']]['options']) ) {
                    $arrAtendimento['index'] ++;

                    // Verifica a opçao correta selecionada que corresponde a mensagem
                    while ($arrListMessage[$arrAtendimento['index']]['option_selected'] != $strMsg) {
                        $arrAtendimento['index'] ++;
                    }

                } else {
                    $arrAtendimento['index'] --;
                }

                $strMsgSended = $arrListMessage[$arrAtendimento['index']]['text'];

            } else {
                $strMsgSended = $arrListMessage[$arrAtendimento['index']]['text'];
            }

            $zapiModel = new ZapiModel();
            // $zapiModel->sendMessage(
            //     $arrInstancia[0]['instancia'], 
            //     $arrInstancia[0]['token'], 
            //     $arrInstancia[0]['client_id'], 
            //     $objData->connectedPhone,
            //     $strMsgSended
            // );

            
            $intNewIndex = $arrAtendimento['index'] +1;

            if ($intNewIndex > $intLengthAtendimento) {
                $strStatus = 'encerrado';
                $intNewIndex = 0;
            } else {
                $strStatus = 'andamento';
            }

            var_dump($intNewIndex);


            $arrAtualizaAtendimento = [
                'chatbot_id' => $arrChatBot['id'],
                'cliente_id' => $intClienteId,
                'mensagem' => mb_substr($strMsgSended, 0, 200),
                'index' => $intNewIndex,
                'status' => $strStatus
            ];
            $atentimentoModel->save($arrAtualizaAtendimento);

            // var_dump($arrAtualizaAtendimento);
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