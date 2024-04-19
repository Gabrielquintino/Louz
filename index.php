<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';
// Inclua o arquivo de rotas
$rotas = require_once __DIR__ . '/config/routes.php';

// Obtenha a rota atual da URL
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$rotaAtual = isset($rotas[$url]) ? $rotas[$url] : '';

// Webhook de recebimento de mensagem
if (strpos($url, '/receive') !== false && strpos($url, '/instancia') !== false) {
    $rotaAtual = false;
    $data = file_get_contents("php://input");
    $objData = json_decode($data);
    $objInstanciaWppController = new App\Controllers\InstanciaWppController;
    $objInstanciaWppController->receiveWebhook(json_decode($data));
} else {
    if ($rotaAtual) {
        // Separe o controlador e o método
        list($controlador, $metodo) = explode('@', $rotaAtual);
    
        if (session_status() != PHP_SESSION_ACTIVE && ( $controlador == "LoginController" && $metodo == 'entrar')) {
            ini_set('session.cookie_lifetime', 0);
            ini_set('session.use_strict_mode', 1);
            $_SESSION['inicio'] = date(format: 'd/m/Y H:i:s');
        }    
    
        // Crie uma instância do controlador
        $controladorCompleto = 'App\Controllers\\' . $controlador;
        $objControlador = new $controladorCompleto();
    
        // Chame o método apropriado
        $objControlador->$metodo();
    } else {
        // Tratamento para rota não encontrada
        echo "Rota não encontrada!";
    }
}