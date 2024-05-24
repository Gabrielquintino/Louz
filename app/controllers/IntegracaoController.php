<?php

namespace App\Controllers;

use App\Models\UsuarioInstanciaModel;
use App\Models\UsuarioModel;
use App\Models\WhatsappApiModel;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use stdClass;
use DateTime;
use Exception;

class IntegracaoController
{

    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "integracao.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }

    public function listagem()
    {
        $objWhatsappApiModel = new WhatsappApiModel();

        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();
        

        $arrInstancia = $objUsuarioInstanciaModel->getInstanceByUser($_SESSION['user_id'], true);

        $arrLista = [
            'data' => []
        ];

        foreach ($arrInstancia as $key => $objInstancia) {

            try {
                $objInstanciaDados = $objWhatsappApiModel->getInstanceData($objInstancia['instancia'], $objInstancia['token']);
            } catch (\Throwable $th) {
                break;
            }


            if ($objInstanciaDados->is_logged) {
                
                if (property_exists($objInstanciaDados, 'client')) {
                    $objInstancia['originalDevice'] = $objInstanciaDados->client->platform;
                    $objInstancia['nome'] = $objInstanciaDados->client->pushname;
                    $objInstancia['phone'] = $objInstanciaDados->client->me->user;
                    $objInstancia['status'] = 'conectado';
                    $objInstancia['id'] = $objInstancia['id'];
                    array_push($arrLista['data'], $objInstancia);
                } else {
                    $objInstancia['originalDevice'] = '-';
                    $objInstancia['nome'] = $objInstancia['instancia'];
                    $objInstancia['phone'] = '-';
                    $objInstancia['status'] = 'disconectado';
                    $objInstancia['id'] = $objInstancia['id'];
                    $objInstanciaDados->status = 'disconectado';
                    array_push($arrLista['data'], $objInstancia);
                }
            } else {
                $objInstancia['originalDevice'] = '-';
                $objInstancia['nome'] = $objInstancia['instancia'];
                $objInstancia['phone'] = '-';
                $objInstancia['id'] = $objInstancia['id'];
                array_push($arrLista['data'], $objInstancia);
            }
        }

        $arrLista['success'] = true;
        echo json_encode($arrLista);
        return $arrLista;
    }

    public function getQrCode() {        
        $objWhatsappApiModel = new WhatsappApiModel();

        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstanceByUser($_SESSION['user_id']);

        try {
            $strQrCode = $objWhatsappApiModel->generateQrCode($arrInstancia[0]["instancia"],  $arrInstancia[0]['token']);

            $arrLista['success'] = true;
            $arrLista['data'] = $strQrCode;
    
            echo json_encode($arrLista);
            return true;
        } catch (Exception $err) {
            http_response_code(500);

            throw $err;
        }
    }

    public function getWppInstance()
    {        
        $objWhatsappApiModel = new WhatsappApiModel();
        
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstanceByUser($_SESSION['user_id']);

        try {
            $objInstancia = $objWhatsappApiModel->getInstanceData($arrInstancia[0]['instancia'], $arrInstancia[0]['token']);

            if ($objInstancia->is_logged) {
                $objUsuarioInstanciaModel->updateInstance('conectado', $arrInstancia[0]['id'], $objInstancia->client->me->user);
                $arrLista['success'] = true;
            } else {
                $arrLista['success'] = false;
            }

            $arrLista['success'] = true;
            $arrLista['data'] = new stdClass();
    
            echo json_encode($arrLista);
            return true;
        } catch (Exception $err) {
            http_response_code(500);
            throw $err;
        }

    }

    public function deleteWppInstance() {
        $objWhatsappApiModel = new WhatsappApiModel();
        
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstanceByUser($_SESSION['user_id']);

        try {
            
            $objWhatsappApiModel->disconnectInstance($arrInstancia[0]['instancia'], $arrInstancia[0]['token']);

            $objUsuarioInstanciaModel->updateInstance('disconectado', $_POST['id'], '' );
            return true;
            
        } catch (Exception $err) {
            http_response_code(500);
            throw $err;
        }
    }
}
