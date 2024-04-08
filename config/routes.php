<?php

// Defina suas rotas aqui
$rotas = [
    '' => 'LoginController@index',
    '/index' => 'LoginController@index',
    '/entrar' => 'LoginController@entrar',
    '/cadastrar' => 'RegistrarController@index',
    '/salvarCadastro' => 'RegistrarController@cadastrar',
    //Integração
    '/integracao' => 'IntegracaoController@index',
    '/listagemIntegracao' => 'IntegracaoController@listagem',
    '/qrcode' => '',
    //ChatBot
    '/chatbot' => 'ChatBotController@index',
    '/listagemChatBot' => 'ChatBotController@listagem',
    '/saveChatbot' => 'ChatBotController@save',
    '/getChatbot' => 'ChatBotController@getData',
    '/deleteChatbot' => 'ChatBotController@delete',


    
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'LoginController@index';
}

return $rotas;
