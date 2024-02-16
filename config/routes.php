<?php

// Defina suas rotas aqui
$rotas = [
    '' => 'LoginController@index',         // Exemplo: página inicial
    '/cadastrar' => 'RegistrarController@cadastrar',
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'LoginController@index';
}

return $rotas;
