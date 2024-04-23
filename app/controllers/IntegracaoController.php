<?php

namespace App\Controllers;

use App\Models\UsuarioInstanciaModel;
use App\Models\UsuarioModel;
use App\Models\ZapiModel;
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
        $objZapiModel = new ZapiModel();

        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstance(true);

        $arrLista = [
            'data' => []
        ];

        foreach ($arrInstancia as $key => $objInstancia) {
            if ($objInstancia['status'] == 'conectado') {
                
                $objInstanciaDados = $objZapiModel->getInstanceData($objInstancia['instancia'], $objInstancia['token']);
                if (property_exists($objInstanciaDados, 'error')) {
                    $objInstancia['originalDevice'] = '-';
                    $objInstancia['nome'] = $objInstancia['instancia'];
                    $objInstancia['phone'] = '-';
                    $objInstancia['status'] = 'desconectado';
                    array_push($arrLista['data'], $objInstancia);
                } else {
                    $objInstanciaDados->status = 'conectado';
                    array_push($arrLista['data'], $objInstanciaDados);
                }
            } else {
                $objInstancia['originalDevice'] = '-';
                $objInstancia['nome'] = $objInstancia['instancia'];
                $objInstancia['phone'] = '-';
                array_push($arrLista['data'], $objInstancia);
            }
        }

        $arrLista['success'] = true;
        echo json_encode($arrLista);
        return true;
    }

    public function getQrCode() {        
        $objZapiModel = new ZapiModel();

        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstance();

        try {
            $strQrCode = $objZapiModel->generateQrCode($arrInstancia[0]["instancia"],  $arrInstancia[0]['token']);

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
        $objZapiModel = new ZapiModel();
        
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstance();

        try {
            $objInstancia = $objZapiModel->getInstanceData($arrInstancia[0]['instancia'], $arrInstancia[0]['token']);

            if (property_exists($objInstancia, 'phone')) {
                $objUsuarioInstanciaModel->updateInstance('conectado', $objInstancia->phone, $arrInstancia[0]['id']);
                $arrLista['success'] = true;
            } else {
                $arrLista['success'] = false;
            }

            $arrLista['data'] = $objInstancia;
    
            echo json_encode($arrLista);
            return true;
        } catch (Exception $err) {
            http_response_code(500);
            throw $err;
        }

    }

    public function deleteWppInstance() {
        $objZapiModel = new ZapiModel();
        
        $objUsuarioInstanciaModel = new UsuarioInstanciaModel();

        $arrInstancia = $objUsuarioInstanciaModel->getInstance();

        try {
            $booDisconected = $objZapiModel->disconnectInstance($arrInstancia[0]['instancia'], $arrInstancia[0]['token']);

            if ($booDisconected) {
                $objUsuarioInstanciaModel->updateInstance('disconectado', '', $arrInstancia[0]['id']);
                return true;
            }
            
        } catch (Exception $err) {
            http_response_code(500);
            throw $err;
        }
    }

    // Outras funções do controlador conforme necessário
}
