<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use App\Models\AtendimentoModel;
use App\Models\CargoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\EventoModel;
use App\Models\FuncionarioModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\UsuarioModel;
use App\Models\WhatsappApiModel;
use DateTime;
use Exception;
use stdClass;

class InstanciaWppController
{

    protected $arrAtendimento = [];
    protected $arrCliente = [];
    protected $arrInstancia = [];
    protected $strSendedPhone = '';


    public function receiveWebhook($objData)
    {
        $WhatsappApiModel = new WhatsappApiModel();
        $url = $_SERVER['REQUEST_URI'];
        $instancia = $this->extractInstanceFromUrl($url);
        $this->arrInstancia = $this->getInstance($instancia);

        $arrUsuario = $this->getUser($this->arrInstancia[0]['usuario_id']);
        $objUser = $arrUsuario['data'][0];
        $strDbUser = 'db_' . $objUser['codigo'];

        $strConnectedPhone = str_replace('@c.us', '', $objData->to);
        $this->strSendedPhone = str_replace('@c.us', '', $objData->from);

        if ($this->strSendedPhone == '554898003266') {
            $arrChatBotData = $this->getChatbot($strConnectedPhone);
            $arrChatBotOrder = json_decode($arrChatBotData['arr_ordem']);
            $arrChatBot = json_decode($arrChatBotData['json'])->cells;
    
            $this->arrCliente = $this->getClient();
            $intClienteId = $this->arrCliente[0]['id'];
    
            $atentimentoModel = new AtendimentoModel();
            $this->arrAtendimento = $atentimentoModel->getAtendimento(
                ['cliente_id', 'chatbot_id'], 
                [$intClienteId, $arrChatBotData['id']]
            );
    
            $arrOpcoes = [];
            $arrSources = [];
    
            if (!empty($objData->body)) {
                $strMsg = $objData->body;
            
                if (empty($this->arrAtendimento) || $this->arrAtendimento[0]['status'] == 'encerrado') {  // #region NOVO ATENDIMENTO
                    // Dispara as mensagens
                    $arrNewAttendance = $this->sendMessages($arrChatBotOrder, $this->arrCliente[0], $this->arrInstancia, $this->strSendedPhone, $arrChatBotData['id'], $intClienteId);
                    if (!empty($arrNewAttendance)) {
                        $atentimentoModel->save($arrNewAttendance);
                    }
                    // #endregion
                } else { // #region ATENDIMENTO JA ABERTO
    
                    foreach ($arrChatBot as $key => $objChat) {
                        if ($objChat->type == "standard.Link" && $objChat->source->id == $this->arrAtendimento[0]['index']) {
                            array_push($arrOpcoes, $objChat->target->id);
                        }
                    }
    
                    $strChatboIndex = '';

                    $booMsgAgendamentoEnviada = false; // Evita que o agendamento seja salvo duas vezes
                    $booDisponibilidade = false;
                    $booEventoAgendado = false;

                    $booReset = true;
                    $booPass = false;
                    $strMsgSended = "Por favor digite um valor que corresponde as opções apresentas.";

                    while ($booReset) {
                        reset($arrChatBotOrder);
                        $booReset = false;
                
                        foreach ($arrChatBotOrder as $key => $objValue) {
                
                            if ($objValue->attrs->label->text == "Envia" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            {
                                $strChatboIndex = json_encode( $objValue->attrs->bodyText->nextElements );
                                $strMsgSended = $objValue->attrs->bodyText->description;
                                $arrNewAttendance = $this->sendMessages($arrChatBotOrder, $this->arrCliente[0], $this->arrInstancia, $this->strSendedPhone, $arrChatBotData['id'], $intClienteId, $objValue->id, $strChatboIndex);
                                if ($arrNewAttendance['status'] == "encerrado") {
                                    sleep(15);
                                    var_dump($strMsgSended);
        
                                    $WhatsappApiModel->sendTextMessage(
                                        $this->arrInstancia[0]['token'], 
                                        $this->strSendedPhone,
                                        "Atendimento finalizado"
                                    );
                                    break;
                                } else {
                                    $atentimentoModel->save($arrNewAttendance);
                                    break;
                                }
                            } 
                            elseif ($objValue->attrs->label->text == "Recebe" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            { // TRATAR AQUI O RECEBIMENTO
                                $strOption = $objValue->attrs->bodyText->opcao;
                                $strOptionSelected = $objValue->attrs->bodyText->valor;
        
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
                                    $booReset = $this->salvaDadosDoCliente($this->arrCliente[0], "", $strMsg, $arrChatBotData['id'], json_encode($objValue->attrs->bodyText->nextElements), false);
                                    break;
                                }
                            } 
                            elseif (
                                $objValue->attrs->label->text == "Salvar" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            {
                                $booReset = $this->salvaDadosDoCliente($this->arrCliente[0], $objValue->attrs->bodyText->campo, $strMsg, $arrChatBotData['id'], json_encode($objValue->attrs->bodyText->nextElements));
                                break;
                            }
                            elseif ($objValue->attrs->label->text == "Agendar" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            {
                                // Enviar a mensagem
                                if (!$booMsgAgendamentoEnviada) {
                                    $objResponse = $WhatsappApiModel->sendTextMessage(
                                        $this->arrInstancia[0]['token'], 
                                        $this->strSendedPhone,
                                        "Informe a data e o horario no formato " . date('d/m/y H:i') . " , vamos conferir a disponibilidade..."
                                    );
                                    $booMsgAgendamentoEnviada = true;
                                    $booReset = true;

                                    var_dump($objResponse);
                                    break;
                                }
                                if (!$booDisponibilidade) {
                                    $booEventoAgendado = $this->agendarEvento($objValue->attrs->bodyText->evento, $intClienteId, $this->arrAtendimento[0]['id'], $strMsg);
                                }

                                if ($booEventoAgendado) {
                                    $booReset = $this->salvaDadosDoCliente($this->arrCliente[0], "", $strMsg, $arrChatBotData['id'], json_encode($objValue->attrs->bodyText->nextElements), false);
                                }

                                break;
                                
                            } 
                            elseif ($objValue->attrs->label->text == "Adicionar tag" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            {
                                $clienteModel = new ClienteModel();
                                $clienteModel->adicionarTag($intClienteId, $objValue->attrs->bodyText->tags);
                                $booReset = $this->salvaDadosDoCliente($this->arrCliente[0], "", $strMsg, $arrChatBotData['id'], json_encode($objValue->attrs->bodyText->nextElements), false);
                                break;
                            } 
                            elseif ($objValue->attrs->label->text == "Transferir" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            {
                                $booReset = $this->transferirAtendimento($this->arrAtendimento[0]['id'], $objValue->attrs->bodyText->setor, $objValue->attrs->bodyText->funcionario);
                                $booReset = $this->salvaDadosDoCliente($this->arrCliente[0], "", $strMsg, $arrChatBotData['id'], json_encode($objValue->attrs->bodyText->nextElements), false, "espera");
                                break;
                            }
                            elseif ($objValue->attrs->label->text == "Fim" && 
                                in_array($objValue->id, json_decode($this->arrAtendimento[0]['index']))) 
                            {
                                
                                $this->salvaDadosDoCliente($this->arrCliente[0], "", $strMsg, $arrChatBotData['id'], json_encode($objValue->id), false, "encerrado");

                                sleep(15);
                                var_dump($strMsgSended);

                                //TODO:: VERIFICAR NA TABELA DE CHATBOT SE PRECISO ENVIAR MENSAGEM DE ATENDIMENTO FINALIZADO
    
                                $WhatsappApiModel->sendTextMessage(
                                    $this->arrInstancia[0]['token'], 
                                    $this->strSendedPhone,
                                    "Atendimento finalizado"
                                );

                                //TODO:: VERIFICAR NA TABELA DE CHATBOT SE PRECISO ENVIAR PESQUISA DE SATISFAÇAO

                                //TODO:: VERIFICAR NA TABELA DE CHATBOT SE EXISTE ALGUM QUE COMEÇA QUANDO TERMINA O CHATBOT EM QUESTAO

                                exit;
                            }
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


    }

    private function extractInstanceFromUrl($url)
    {
        $posicaoInstancia = strpos($url, '/instancia/') + strlen('/instancia/');
        return substr($url, $posicaoInstancia, strpos($url, '/', $posicaoInstancia) - $posicaoInstancia);
    }

    private function getInstance(string $pInstancia): array
    {
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();
        $this->arrInstancia = $objUsuarioInstanciaModel->getInstance(true, $pInstancia);
        if (empty($this->arrInstancia)) {
            throw new Exception("Instancia nao encontrada", 1);
        }
        return $this->arrInstancia;
    }

    private function getUser($pUserId): array
    {
        $objUsuario = new UsuarioModel();
        $arrUsuario = $objUsuario->obterUsuarioPorId($pUserId);
        if (empty($arrUsuario)) {
            throw new Exception("Instancia nao encontrada", 1);
        }
        return $arrUsuario;
    }

    private function getChatbot($strConnectedPhone): array
    {
        $chatBotModel = new ChatBotModel();
        $arrChatBotData = $chatBotModel->getChatBot('integration_phone', $strConnectedPhone);
        if (empty($arrChatBotData)) {
            throw new Exception("Chatbot nao configurado para a instancia", 1);
        }
        return $arrChatBotData;
    }

    private function getClient(): array
    {
        $clienteModel = new ClienteModel();
        $this->arrCliente = $clienteModel->getClient('telefone', $this->strSendedPhone);
    
        if (empty($this->arrCliente)) {
            $intClienteId = $clienteModel->save([
                'nome' => '',
                'telefone' => $this->strSendedPhone
            ]);
    
            // Atualiza o array de cliente com os dados recém-criados
            $this->arrCliente = [
                [
                    'id' => $intClienteId,
                    'nome' => '',
                    'telefone' => $this->strSendedPhone,
                    'email' => ''
                ]
            ];
        }
    
        return $this->arrCliente;
    }    

    private function sendMessages($pArrChatBotOrder, $parrCliente, $pArrInstancia, $pStrSendedPhone, $pChatbotId, $pClienteId, $pStartIn = false, $strChatbotId = '')  {
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

                // Array de substituições
                $arrSubstituicoes = [
                    '{{nome}}' => $parrCliente['nome'],
                    '{{email}}' => $parrCliente['email']
                ];

                $strMsg = $objChat->attrs->bodyText->description;
                // Substituir os placeholders na string
                $strMsgSended = str_replace(array_keys($arrSubstituicoes), array_values($arrSubstituicoes), $strMsg);

                // CHAMA FUNCAO QUE ENVIA A MSG
                if ($pStrSendedPhone == '554898003266') {
                    $strMsgSended = preg_replace("/\r|\n/", '\n', $strMsgSended);
                    $WhatsappApiModel = new WhatsappApiModel();

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
                    'index' => json_encode( $objChat->attrs->bodyText->nextElements ),
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
            elseif (property_exists($objChat->attrs, 'root') && $objChat->attrs->label->text != 'Envia') {
                break;
            }
        }

        return $arrNewAttendance;
    }

    private function salvaDadosDoCliente($parrCliente, $pstrField, $pStrValue, $pChatbotId, $pObjChatId, $booSaveClient = true, $pStatus = "andamento") : bool {

        try {

            if ($booSaveClient) {
                $clienteModel = new ClienteModel();
                $clienteModel->save([
                    'id' => $parrCliente['id'],
                    $pstrField => $pStrValue
                ]);

                $this->arrCliente = $clienteModel->getClient('id', $parrCliente['id']);
            }

            // Atualiza o atendimento
            $arrAttendance = [
                'chatbot_id' => $pChatbotId,
                'cliente_id' => $parrCliente['id'],
                'funcionarios_id' => 0,
                'mensagem' => '',
                'index' => $pObjChatId,
                'status' => $pStatus
            ];

            $atentimentoModel = new AtendimentoModel();
            $atentimentoModel->save($arrAttendance);

            $this->arrAtendimento = $atentimentoModel->getAtendimento(
                ['cliente_id', 'chatbot_id'], 
                [$parrCliente['id'], $pChatbotId]
            );
    
            return true;
        } catch (Exception $err) {
            return false;
        }

    }

    private function transferirAtendimento($pAtendimentoId, $pCargoId = null, $pFuncionarioId = null): bool {
        
        try {
            if (empty($pFuncionarioId)) {
                $funcionarioModel = new FuncionarioModel();
                $arrFuncionarios = $funcionarioModel->listagem(true, $pCargoId);
    
                $funcionarioEscolhido = array_rand($arrFuncionarios);
                $pFuncionarioId = $arrFuncionarios[$funcionarioEscolhido]['id'];
            }
            
            $atentimentoModel = new AtendimentoModel();
            $atentimentoModel->save([
                'id' => $pAtendimentoId,
                'funcionarios_id' => $pFuncionarioId
            ]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    private function agendarEvento($pEventoId, $pClienteId, $pAtendimentoId, $pData) : bool {
        try {
            $WhatsappApiModel = new WhatsappApiModel();

            $eventoModel = new EventoModel();
            $arrEvento = $eventoModel->get($pEventoId);
    
            // Horários disponíveis configurados (normalmente viria do banco de dados)
            $horariosDisponiveis = json_decode($arrEvento[0]['horarios_semana'], true);
    
            // Parseia a data e a hora do parâmetro $pData
            $dataObj = DateTime::createFromFormat('d/m/y H:i', $pData);
            if (!$dataObj) {
                throw new Exception('Formato de data inválido');
            }
    
            // Obtém o dia da semana (em português)
            $diasSemana = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
            $diaSemana = $diasSemana[$dataObj->format('w')];
    
            // Verifica se existe configuração para o dia da semana
            if (!isset($horariosDisponiveis[$diaSemana])) {
                $strSendMsg = "Não há horários configurados para este dia. Horários disponíveis da semana:\n";
                foreach ($horariosDisponiveis as $dia => $intervalo) {
                    $strSendMsg .= "$dia: $intervalo\n";
                }
                $WhatsappApiModel->sendTextMessage(
                    $this->arrInstancia[0]['token'], 
                    $this->strSendedPhone,
                    preg_replace("/\r|\n/", '\n', $strSendMsg)
                );
                var_dump($strSendMsg);

                return false;
            }
    
            // Obtém os horários configurados para o dia
            list($horaInicio, $horaFim) = explode('-', $horariosDisponiveis[$diaSemana]);
    
            // Converte as horas configuradas para DateTime
            $horaInicioObj = DateTime::createFromFormat('H:i', $horaInicio);
            $horaFimObj = DateTime::createFromFormat('H:i', $horaFim);
    
            // Verifica se a hora está dentro do intervalo configurado
            $horaAtual = DateTime::createFromFormat('H:i', $dataObj->format('H:i'));
    
            if ($horaAtual >= $horaInicioObj && $horaAtual <= $horaFimObj) {
                // Verifica se já existe um evento agendado no horário solicitado
                $agendamentoModel = new AgendamentoModel();
                $arrAgendamentos = $agendamentoModel->listagem();
    
                foreach ($arrAgendamentos as $agendamento) {
                    $dataAgendada = DateTime::createFromFormat('Y-m-d H:i:s', $agendamento['data']);
                    if ($dataAgendada == $dataObj) {
                        $strSendMsg = "Horário já agendado. Horários disponíveis sem agendamentos da semana:\n";
    
                        // Encontra horários disponíveis sem agendamentos
                        foreach ($horariosDisponiveis as $dia => $intervalo) {
                            list($start, $end) = explode('-', $intervalo);
                            $startObj = DateTime::createFromFormat('H:i', $start);
                            $endObj = DateTime::createFromFormat('H:i', $end);
    
                            $isAvailable = true;
    
                            foreach ($arrAgendamentos as $agendamentoInterno) {
                                $dataAgendadaInterna = DateTime::createFromFormat('Y-m-d H:i:s', $agendamentoInterno['data']);
                                $diaSemanaInterno = $diasSemana[$dataAgendadaInterna->format('w')];
    
                                if ($diaSemanaInterno == $dia && $dataAgendadaInterna >= $startObj && $dataAgendadaInterna <= $endObj) {
                                    $isAvailable = false;
                                    break;
                                }
                            }
    
                            if ($isAvailable) {
                                $strSendMsg .= "$dia: $intervalo\n";
                            }
                        }

                        $WhatsappApiModel->sendTextMessage(
                            $this->arrInstancia[0]['token'], 
                            $this->strSendedPhone,
                            preg_replace("/\r|\n/", '\n', $strSendMsg)
                        );

                        var_dump($strSendMsg);

                        return false;
                    }
                }
    
                // Dados para salvar o agendamento
                $arrData = [
                    'eventos_id' => $pEventoId,
                    'clientes_id' => $pClienteId,
                    'atendimentos_id' => $pAtendimentoId,
                    'data' => $dataObj
                ];
    
                // Salvando o agendamento
                try {
                    $agendamentoModel->save($arrData, false);
                    return true;
                } catch (Exception $e) {
                    throw new Exception($e->getMessage(), 1);
                    print($e->getMessage());

                    return false;
                }
    
                return true;
            } else {
                $strSendMsg = "Horário fora do intervalo configurado. Horários disponíveis da semana:\n";
                foreach ($horariosDisponiveis as $dia => $intervalo) {
                    $strSendMsg .= "$dia: $intervalo\n";
                }

                $WhatsappApiModel->sendTextMessage(
                    $this->arrInstancia[0]['token'], 
                    $this->strSendedPhone,
                    preg_replace("/\r|\n/", '\n', $strSendMsg)
                );

                var_dump($strSendMsg);

                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
}