<?php

namespace App\Controllers;

use App\Models\AtendimentoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\UsuarioModel;
use App\Models\WhatsappApiModel;
use Exception;
use stdClass;

class InstanciaWppController
{

    public function receiveWebhook($objData)
    {
        // Obtém a URL atual
        $url = $_SERVER['REQUEST_URI'];

        #region Dados necessarios

        // Encontra a posição de "/instancia/" na URL
        $posicaoInstancia = strpos($url, '/instancia/') + strlen('/instancia/');

        // Extrai o valor da instância da URL
        $instancia = substr($url, $posicaoInstancia, strpos($url, '/', $posicaoInstancia) - $posicaoInstancia);

        $arrInstancia = $this->getInstance($instancia);

        // Obtem dados do Usuario
        $arrUsuario = $this->getUser($arrInstancia[0]['usuario_id']);

        $objUser = $arrUsuario['data'][0];
        $strUserCode = $objUser['codigo'];
        $strDbUser = 'db_' . $strUserCode;

        $strConnectedPhone = str_replace('@c.us', '', $objData->to);
        $strSendedPhone = str_replace('@c.us', '', $objData->from);

        // Obtem dados do ChatBot
        $arrChatBotData = $this->getChatbot($strConnectedPhone);

        $objChatBotJson = json_decode($arrChatBotData['json']);
        $arrChatBotOrder = json_decode($arrChatBotData['arr_ordem']);
        $arrChatBot = $objChatBotJson->cells;

        #endregion

        // Obtém dados do cliente
        $intClienteId = $this->getClient($strSendedPhone);

        $atentimentoModel = new AtendimentoModel();
        $arrAtendimento = $atentimentoModel->getAtendimento(['cliente_id', 'chatbot_id'], [$intClienteId, $arrChatBotData['id']]);

        $arrOpcoes = [];

        if (!empty($objData->body)) {
            $strMsg = $objData->body;
        
            if (empty($arrAtendimento) || $arrAtendimento[0]['status'] != 'andamento') {  // #region NOVO ATENDIMENTO
                // Dispara as mensagens
                $arrNewAttendance = $this->sendMessages($arrChatBotOrder, $arrInstancia, $strSendedPhone, $arrChatBotData['id'], $intClienteId);
                if (!empty($arrNewAttendance)) {
                    $atentimentoModel->save($arrNewAttendance);
                }
                // #endregion
            } else { // #region ATENDIMENTO JA ABERTO

                foreach ($arrChatBot as $key => $objChat) {
                    if ($objChat->type == "standard.Link" && $objChat->source->id == $arrAtendimento[0]['index']) {
                        array_push($arrOpcoes, $objChat->target->id);
                    }
                }

                $strChatboIndex = '';
                foreach ($arrChatBotOrder as $key => $objValue) {
                    if (
                        property_exists($objValue->attrs, 'bodyText') &&
                        property_exists($objValue->attrs->bodyText, 'previousElement') &&
                        property_exists($objValue->attrs->bodyText->previousElement, 'prevId') &&
                        in_array($objValue->attrs->bodyText->previousElement->prevId, $arrOpcoes)
                    ) {

                        $strOption = $objValue->attrs->bodyText->previousElement->opcao;
                        $strOptionSelected = $objValue->attrs->bodyText->previousElement->valor;

                        switch ($strOption) {
                            case 'igual':
                                $booPass = $strMsg == $strOptionSelected;
                                break;
                            case 'diferente':
                                $booPass = $strMsg != $strOptionSelected;
                                break;
                            case 'contem':
                                $booPass = str_contains($strMsg, $strOptionSelected);
                                break;
                            case 'naoContem':
                                $booPass = !str_contains($strMsg, $strOptionSelected);
                                break;
                            default:
                                $booPass = false;
                                break;
                        }

                        if ($booPass) {
                            $strChatboIndex = $objValue->id;
                            $strMsgSended = $objValue->attrs->bodyText->description;
                            break;
                        } else {
                            $strMsgSended = "Por favor digite um valor que corresponde as opções apresentas.";
                        }


                    }
                }

                if ($booPass) {
                    $arrNewAttendance = $this->sendMessages($arrChatBotOrder, $arrInstancia, $strSendedPhone, $arrChatBotData['id'], $intClienteId, $strChatboIndex);
                    if (!empty($arrNewAttendance)) {
                        $atentimentoModel->save($arrNewAttendance);
                        if ($arrNewAttendance['status'] == "encerrado") {
                            $WhatsappApiModel = new WhatsappApiModel();
                            sleep(15);
                            var_dump($strMsgSended);

                            $WhatsappApiModel->sendTextMessage(
                                $arrInstancia[0]['token'], 
                                $strSendedPhone,
                                "Atendimento finalizado"
                            );
                        }
                    }
                } else {
                    if ($strSendedPhone == '554898003266') {
                        $strMsgSended = preg_replace("/\r|\n/", '\n', $strMsgSended);
                        $WhatsappApiModel = new WhatsappApiModel();
                        var_dump($strMsgSended);
                        $WhatsappApiModel->sendTextMessage(
                            $arrInstancia[0]['token'], 
                            $strSendedPhone,
                            $strMsgSended
                        );
                    }
                }
            }
        }

        // Lista de botao
        if (property_exists($objData, 'buttonsResponseMessage')) {
            # code...
        }
        // Lista de opçao
        if (property_exists($objData, 'listResponseMessage')) {
            # code...
        }

        $objResult = new stdClass();
        $objResult->success = true;

        print_r($objResult);
    }

    // Retorna a instancia
    public function getInstance(string $pInstancia): array
    {
        // Obtem dados da instancia
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();
        $arrInstancia = $objUsuarioInstanciaModel->getInstance(pStrInstance: $pInstancia);
        if (empty($arrInstancia)) {
            throw new Exception("Instancia nao encontrada", 1);
            return false;
        }
        return $arrInstancia;
    }

    // Retorna o usuario
    public function getUser($pUserId): array
    {
        $objUsuario = new UsuarioModel();
        $arrUsuario = $objUsuario->obterUsuarioPorId($pUserId);
        if (empty($arrUsuario)) {
            throw new Exception("Instancia nao encontrada", 1);
            return false;
        }

        return $arrUsuario;
    }

    // Retorna o chatbot
    public function getChatbot($strConnectedPhone): array
    {
        $chatBotModel = new ChatBotModel();
        $arrChatBotData = $chatBotModel->getChatBot('integration_phone', $strConnectedPhone);
        if (empty($arrChatBotData)) {
            throw new Exception("Chatbot nao configurado para a instancia", 1);
            return false;
        }
        return $arrChatBotData;
    }

    // Retorna o id do Cliente
    public function getClient($strSendedPhone): int
    {
        $clienteModel = new ClienteModel();
        $arrCliente = $clienteModel->getClient('telefone', $strSendedPhone);

        if (empty($arrCliente)) {
            // Cliente novo
            $intClienteId = $clienteModel->save([
                'nome' => '',
                'telefone' => $strSendedPhone
            ]);
        } else {
            $intClienteId = $arrCliente[0]['id'];
        }
        return $intClienteId;
    }

    public function sendMessages($pArrChatBotOrder, $pArrInstancia, $pStrSendedPhone, $pChatbotId, $pClienteId, $pStartIn = false)  {
        if ($pStartIn) {
            foreach ($pArrChatBotOrder as $key => $objChat) {
                if ($objChat->id == $pStartIn) {
                    $pArrChatBotOrder = array_slice($pArrChatBotOrder, $key);
                }
            }
        }

        $arrNewAttendance = [];
        foreach ($pArrChatBotOrder as $key => $objChat) {
            if (property_exists($objChat->attrs, 'root') && $objChat->attrs->root->title == 'Mensagem Enviada') {
                $strMsgSended = $objChat->attrs->bodyText->description;
                // CHAMA FUNCAO QUE ENVIA A MSG
                if ($pStrSendedPhone == '554898003266') {
                    $strMsgSended = preg_replace("/\r|\n/", '\n', $strMsgSended);
                    $WhatsappApiModel = new WhatsappApiModel();
                    var_dump($strMsgSended);

                    $WhatsappApiModel->sendTextMessage(
                        $pArrInstancia[0]['token'], 
                        $pStrSendedPhone,
                        $strMsgSended
                    );
                }

                // ATUALIZA O ATENDIMENTO
                $arrAttendance = [
                    'chatbot_id' => $pChatbotId,
                    'cliente_id' => $pClienteId,
                    'funcionarios_id' => 0,
                    'mensagem' => mb_substr($strMsgSended, 0, 200),
                    'index' => $objChat->id,
                    'status' => 'andamento'
                ];
                $arrNewAttendance = $arrAttendance;
            } elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text == 'Foto') {
                if ( filter_var($objChat->attrs->bodyText->arquivo, FILTER_VALIDATE_URL) !== false ) {
                    $WhatsappApiModel = new WhatsappApiModel();
                    $WhatsappApiModel->sendFile($pArrInstancia[0]['token'], $pStrSendedPhone, $objChat->attrs->bodyText->arquivo);
                }
            } elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text == 'Audio') {
                if ( filter_var($objChat->attrs->bodyText->arquivo, FILTER_VALIDATE_URL) !== false ) {
                    $WhatsappApiModel = new WhatsappApiModel();
                    $WhatsappApiModel->sendFile($pArrInstancia[0]['token'], $pStrSendedPhone, $objChat->attrs->bodyText->arquivo);
                }
            } elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text == 'Arquivo') {
                if ( filter_var($objChat->attrs->bodyText->arquivo, FILTER_VALIDATE_URL) !== false ) {
                    $WhatsappApiModel = new WhatsappApiModel();
                    $WhatsappApiModel->sendFile($pArrInstancia[0]['token'], $pStrSendedPhone, $objChat->attrs->bodyText->arquivo);
                }
            } elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text == 'Localizaçao') {
                print_r("ENVIAR Localizaçao"); echo "<br/>";
            } elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text == 'Fim') {
                $strMsgSended = "Atendimento finalizado";
                $arrAttendance = [
                    'chatbot_id' => $pChatbotId,
                    'cliente_id' => $pClienteId,
                    'funcionarios_id' => 0,
                    'mensagem' => mb_substr($strMsgSended, 0, 200),
                    'index' => $objChat->id,
                    'status' => 'encerrado'
                ];
                $arrNewAttendance = $arrAttendance;
                break;
            } 
            elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text == 'Recebe') {
                break;
            }
        }

        return $arrNewAttendance;
    }
}
