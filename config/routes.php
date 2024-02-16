<?php

// Defina suas rotas aqui
$rotas = [
    '' => 'CloneController@index',         // Exemplo: página inicial
    '/import' => 'CloneController@importSite',
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'CloneController@index';
}

return $rotas;
