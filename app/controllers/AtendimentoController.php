<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use App\Models\AtendimentoModel;
use App\Models\ChatBotModel;
use App\Models\ClienteModel;
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

        $result = $atendimentotModel->listagem();

        $arrLista['success'] = true;
        $arrLista['data'] = $result;

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

        if (!empty($_POST['funcionarios_id'])) {
            $atendimentotModel = new AtendimentoModel();

            $arrAttendance = [
                'id' => $_POST['id'],
                'funcionarios_id' => $_POST['funcionarios_id'],
            ];
    
            // Salva o atendimento
            $result = $atendimentotModel->save($arrAttendance);
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
            $clienteModal = new ClienteModel();
            $clienteModal->save($arrCliente);
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
}