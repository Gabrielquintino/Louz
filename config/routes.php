<?php

// Defina suas rotas aqui
$rotas = [
    '' => 'LoginController@index',
    '/index' => 'LoginController@index',
    '/entrar' => 'LoginController@entrar',
    '/cadastrar' => 'RegistrarController@index',
    '/cadastrar/salvar' => 'RegistrarController@cadastrar',
    #region Integração
    '/integracao' => 'IntegracaoController@index',
    '/integracao/listagem' => 'IntegracaoController@listagem',
    '/integracao/qrcode' => 'IntegracaoController@getQrCode',
    '/integracao/get' => 'IntegracaoController@getWppInstance',
    '/integracao/delete' => 'IntegracaoController@deleteWppInstance',
    #endregion
    #region ChatBot
    '/chatbot' => 'ChatBotController@index',
    '/chatbot/listagem' => 'ChatBotController@listagem',
    '/chatbot/save' => 'ChatBotController@save',
    '/chatbot/get' => 'ChatBotController@getData',
    '/chatbot/delete' => 'ChatBotController@delete',
    '/chatbot/upload' => 'ChatBotController@upload',

    #endregion
    #region Atendimentos
    '/atendimento' => 'AtendimentoController@index',
    '/atendimento/listagem' => 'AtendimentoController@listagem',
    '/atendimento/save' => 'AtendimentoController@save',
    '/atendimento/get' => 'AtendimentoController@getData',
    '/atendimento/delete' => 'AtendimentoController@delete',
    '/atendimento/sendMessage' => 'AtendimentoController@sendMessage',
    '/atendimento/encerrar' => 'AtendimentoController@encerrar',
    #endregion

    #region Funcionarios
    '/funcionarios' => 'FuncionarioController@index',
    '/funcionarios/listagem' => 'FuncionarioController@listagem',
    '/funcionarios/save' => 'FuncionarioController@save',
    '/funcionarios/get' => 'FuncionarioController@get',
    '/funcionarios/delete' => 'FuncionarioController@delete',
    #endregion

    #region Cargos
    '/cargos' => 'CargosController@index',
    '/cargos/listagem' => 'CargosController@listagem',
    '/cargos/save' => 'CargosController@save',
    '/cargos/get' => 'CargosController@get',
    '/cargos/delete' => 'CargosController@delete',
    #endregion

    #region Eventos
    '/eventos' => 'EventoController@index',
    '/eventos/listagem' => 'EventoController@listagem',
    '/eventos/save' => 'EventoController@save',
    '/eventos/get' => 'EventoController@get',
    '/eventos/delete' => 'EventoController@delete',
    #endregion

    #region Funil
    '/funil' => 'FunilController@index',
    #endregion
    #region CRM
    '/crm' => 'CrmController@index',
    '/crm/list' => 'CrmController@list',
    '/crm/save' => 'CrmController@save',
    '/crm/edit' => 'CrmController@edit',
    '/crm/delete' => 'CrmController@delete',
    '/crm/history' => 'CrmController@history',

    #endregion
    #region Fornecedores
    '/fornecedores' => 'FornecedoresController@index',
    #region
    '/produtos' => 'ProdutosController@index',
    '/estoque' => 'EstoqueController@index',
    '/vendas' => 'VendasController@index',





    
    '/instancia/{instancia}/receive' => 'InstanciaWppController@receiveWebhook'


    
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'LoginController@index';
}

return $rotas;
