<?php

namespace App\Controllers;

use App\Models\ChatBotModel;
use Exception;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class ContaController
{
    /**
     * Retorna o arquivo
     */
    public function index()
    {
        // Lógica de roteamento e controle aqui
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
            $html = "conta.html";
            include_once __DIR__ . '/../views/index.php';
        } else {
            // Redireciona
            header("Location: /");
        }
    }
}