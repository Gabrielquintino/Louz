<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use App\Models\AtendimentoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
use App\Models\EtapaModel;
use App\Models\FuncionarioModel;
use App\Models\UsuarioInstanciaModel;
use App\Models\WhatsappApiModel;
use Exception;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class AtendimentoController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "atendimento.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    /**
     * Lista os chatbots configurados
     * 
     * since 18/05/24
     * @author Gabriel Quintino <gabrielv.quintino@gmail.com>
     */
    public function listagem() {
        $atendimentotModel = new AtendimentoModel();
        $WhatsappApiModel = new WhatsappApiModel();
        $usuarioInstanciaModel = new UsuarioInstanciaModel();
        $clienteModel = new ClienteModel();
        $chatBotModel = new ChatBotModel();

        $arrInstancia = $usuarioInstanciaModel->getInstanceByUser($_SESSION['user_id']);
        $arrAtendimento = $atendimentotModel->listagem();

        $arrClientes = $clienteModel->list();

        $page = isset($_POST['page']) ? $_POST['page'] : "1";

        $arrConversas = [];

        if (!empty($arrInstancia)) {
            $arrConversas = $WhatsappApiModel->getChats($arrInstancia[0]["token"], $page, "10");
        }

        $arrTelefones = [];

        foreach ($arrClientes as $key => $cliente) {
            array_push($arrTelefones, $cliente["telefone"]);
        }

        if (property_exists($arrConversas, 'chats')) {
            foreach ($arrConversas->chats as $key => $conversa) {
                if (!in_array($conversa->id->user, $arrTelefones) ) { // Salva o cliente e o atendimento
                    $arrParametrosCliente = [
                        'nome' => $conversa->name,
                        'email' => null,
                        'telefone' => $conversa->id->user,
    
                    ];
                    $intClientId = $clienteModel->save($arrParametrosCliente);
    
                    $objChatBot = $chatBotModel->getChatBot('default', "sim");
    
                    $arrParametrosAtendimento = [
                        'chatbot_id' => $objChatBot["id"],
                        'cliente_id' => $intClientId,
                        'funcionarios_id' => 0,
                        'mensagem' => "",
                        'index' => "",
                        'status' => 'encerrado',
                        'data' => date('Y-m-d H:i:s')
                    ];
                    if ($conversa->unreadCount > 0) {
                        $arrParametrosAtendimento['status'] = 'andamento';
                    }
                    $arrParametrosAtendimento['id'] = $atendimentotModel->save($arrParametrosAtendimento);
    
                    array_push($arrAtendimento, $arrParametrosAtendimento);

                    // TODO:: SALVAR ETAPA

                    $etapaModel = new EtapaModel();
                    $arrEtapas = $etapaModel->list();

                    $intEtapaId = $arrEtapas[0]['id'];

                    $etapaModel->saveLog($intClientId, $intEtapaId);


                }

                $arrAtendimento[$key]['unread'] = $conversa->unreadCount > 99 ? "99+" :  ($conversa->unreadCount > 0 ? $conversa->unreadCount . "+" : null) ;
            }
        }

        $arrLista['success'] = true;
        $arrLista['data'] = $arrAtendimento;

        echo json_encode($arrLista);
        return true;
    }

    public function sendMessage() {

        $objInstancia = new UsuarioInstanciaModel();

        $arrInstancia = $objInstancia->getInstanceByUser( $_SESSION['user_id'], true );

        if (empty($arrInstancia)) {
            throw new Exception("Error Processing Request", 1);
            return;
        }
        
        $strMsgSended = preg_replace("/\r|\n/", '\n', $_POST['message']);

        try {
            $WhatsappApiModel = new WhatsappApiModel();        
            $objResponse = $WhatsappApiModel->sendTextMessage(
                $arrInstancia[0]['token'], 
                $_POST['telefone'],
                $strMsgSended
            );

            print_r([
                'success' => true,
                'data' => $objResponse
            ]); 

            return [
                'success' => true,
                'data' => $objResponse
            ];
            
        } catch (Exception $err) {
            throw new Exception($err, 1);
            return;
        }
    }

    public function save() {

        $result = [];

        if (!empty($_POST['funcionarios_id'])) {
            $atendimentotModel = new AtendimentoModel();

            $arrAttendance = [
                'id' => $_POST['id'],
                'funcionarios_id' => $_POST['funcionarios_id'],
            ];
    
            // Salva o atendimento
            $result = $atendimentotModel->save($arrAttendance);

            $funcionarioModel = new FuncionarioModel();
            $arrFuncionario = $funcionarioModel->get($_POST['funcionarios_id']);

            $arrFuncionarioAtual = $funcionarioModel->get($_SESSION['usuario'], 'f.email');

            $atendimentotModel->saveLog((int) $_POST['cliente_id'], (int) $arrFuncionarioAtual[0]['id'], "Transferido para " . $arrFuncionario[0]['nome']);
        }

        if (!empty($_POST['observacao'])) { // TODO: BUSCAR O FUNCIONARIO CORRETO
            $atendimentotModel = new AtendimentoModel();
            $atendimentotModel->saveLog((int) $_POST['cliente_id'], (int) 15, $_POST['observacao']);
        }

        if (!empty($_POST['evento_id']) && !empty($_POST['dataHora'])) {
            $arrAgendameneto = [
                'eventos_id' => $_POST['evento_id'],
                'clientes_id' => $_POST['cliente_id'],
                'atendimentos_id' => $_POST['id'],
                'data' => $_POST['dataHora']
            ];
            $agendamentoModel = new AgendamentoModel();
            $agendamentoModel->save($arrAgendameneto);
        }

        if ( (bool) $_POST['boolTag']) {
            $arrCliente = [
                'id' => $_POST['cliente_id'],
                'tags' => $_POST['tags']
            ];
            $clienteModel = new ClienteModel();
            $clienteModel->save($arrCliente);
        }

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

        echo json_encode($arrLista);
        return true;
    }

    public function encerrar() {
        $atendimentotModel = new AtendimentoModel();

        $arrAttendance = [
            'id' => $_POST['id'],
            'funcionarios_id' => $_POST['funcionarios_id'],
            'status' => 'encerrado'
        ];

        // Salva o atendimento
        $result = $atendimentotModel->save($arrAttendance);        
    }

    public function noPeriodo() {
        $atendimentotModel = new AtendimentoModel();
        $arrAtendimento = $atendimentotModel->noPeriodo($_POST['data']);

        $arrLista['success'] = true;
        $arrLista['data'] = $arrAtendimento;

        echo json_encode($arrLista);
        return true;
    }
}