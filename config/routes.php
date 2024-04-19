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
    '/getQrCode' => 'IntegracaoController@getQrCode',
    '/getWppInstance' => 'IntegracaoController@getWppInstance',
    '/deleteWppInstance' => 'IntegracaoController@deleteWppInstance',
    //ChatBot
    '/chatbot' => 'ChatBotController@index',
    '/listagemChatBot' => 'ChatBotController@listagem',
    '/saveChatbot' => 'ChatBotController@save',
    '/getChatbot' => 'ChatBotController@getData',
    '/deleteChatbot' => 'ChatBotController@delete',
    
    '/instancia/{instancia}/receive' => 'InstanciaWppController@receiveWebhook'


    
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'LoginController@index';
}

return $rotas;
