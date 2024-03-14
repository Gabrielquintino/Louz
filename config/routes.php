<?php

// Defina suas rotas aqui
$rotas = [
    '' => 'LoginController@index',
    '/index' => 'LoginController@index',
    '/entrar' => 'LoginController@entrar',
    '/cadastrar' => 'RegistrarController@cadastrar',
    '/integracao' => 'IntegracaoController@index',
    '/listagemIntegracao' => 'IntegracaoController@listagem',
    '/qrcode' => ''
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'LoginController@index';
}

return $rotas;
