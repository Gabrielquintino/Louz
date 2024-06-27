<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Controllers\SessaoManager;

class LoginController
{

    public function index()
    {
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {

            if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] == 'session=destroy') {
                session_destroy();
                header("Location: /");
            }

            $html = 'index.html';
            include_once __DIR__ . '/../views/index.php';
        } else {
            include_once __DIR__ . '/../views/authentication-login.html';
        }
    }

    public function entrar()
    {
        $sessao = new SessaoManager();
        
        if (!isset($_POST['email']) || !isset($_POST['senha'])) {
            return ['success' => false];
        }

        if (empty($_POST['email']) || empty($_POST['senha'])) {
            return ['success' => false];
        } 

        $usuarioModel = new UsuarioModel();
        $resultado = $usuarioModel->login($_POST['email'], $_POST['senha']);

        if ($resultado["exist"]) {
            // Credenciais corretas, define a variável de sessão indicando que o usuário está logado
            $_SESSION['usuario_logado'] = true;
            $_SESSION['usuario'] = $_POST['email'];
            $_SESSION['usuario_nome'] = $resultado["data"][0]["nome"];
            $_SESSION['db_usuario'] = 'db_' . $resultado["data"][0]["codigo"];
            $_SESSION['codigo_usuario'] = $resultado["data"][0]["codigo"];
            $_SESSION['user_id'] = $resultado["data"][0]["id"];
            
            echo json_encode(['success' => true]);
            return true;

        } else {
            // Credenciais incorretas, redireciona para a página de login com uma mensagem de erro
            echo json_encode(['success' => false]);
            return true;
        }
    }

    public function verificarEtapas() {
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {


            $usuarioModel = new UsuarioModel();
            $arrData = $usuarioModel->verificarEtapas($_SESSION['user_id']);

            $arrData['success'] = true;
            echo json_encode($arrData);
            return true;

        } else {
            // Redireciona
            header("Location: /");
        }
    }
}