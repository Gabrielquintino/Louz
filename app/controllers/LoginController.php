<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Controllers\SessaoManager;

class LoginController
{

    public function index()
    {
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']) {
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

        if ($resultado) {
            // Credenciais corretas, define a variável de sessão indicando que o usuário está logado
            $_SESSION['usuario_logado'] = true;
            $_SESSION['usuario'] = $_POST['email'];
            
            echo json_encode(['success' => true]);
            return true;

        } else {
            // Credenciais incorretas, redireciona para a página de login com uma mensagem de erro
            echo json_encode(['success' => false]);
            return true;
        }
    }
}