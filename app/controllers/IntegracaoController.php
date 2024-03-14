<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use stdClass;

class IntegracaoController
{

    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        $html = "integracao.html";
        include_once __DIR__ . '/../views/index.php';
    }

    public function listagem()
    {

        $folderPath = 'C:/code/GestaoFacil/resources/js/.wwebjs_auth';
        $prefix = 'session-';

        $folders = array_filter(scandir($folderPath), function ($item) use ($prefix) {
            return is_dir('C:/code/GestaoFacil/resources/js/.wwebjs_auth' . '/' . $item) && strpos($item, $prefix) === 0;
        });

        $arrLista = [
            'data' => [],
            'success' => false
        ];


        foreach ($folders as $folder) {
            $folderPath .= '/' . $folder;
            $objItem = new stdClass();
            $objItem->nome = str_replace($prefix, '', $folder);

            array_push($arrLista['data'], $objItem);
        }

        $arrLista['success'] = true;


        echo json_encode($arrLista);
        return true;
    }

    public function cadastrarWhatsapp()
    {

        $arrData = $_POST;

        $cloneModel = new UsuarioModel();
        $result = $cloneModel->save($arrData);

        return $result;
    }

    // Outras funções do controlador conforme necessário
}
