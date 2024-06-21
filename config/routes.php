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
    '/cargos' => 'CargoController@index',
    '/cargos/listagem' => 'CargoController@listagem',
    '/cargos/save' => 'CargoController@save',
    '/cargos/get' => 'CargoController@get',
    '/cargos/delete' => 'CargoController@delete',
    #endregion

    #region Eventos
    '/eventos' => 'EventoController@index',
    '/eventos/listagem' => 'EventoController@listagem',
    '/eventos/save' => 'EventoController@save',
    '/eventos/get' => 'EventoController@get',
    '/eventos/delete' => 'EventoController@delete',
    #endregion

    #region Eventos
    '/agendamentos' => 'AgendamentoController@index',
    '/agendamentos/listagem' => 'AgendamentoController@listagem',
    '/agendamentos/save' => 'AgendamentoController@save',
    '/agendamentos/get' => 'AgendamentoController@get',
    '/agendamentos/delete' => 'AgendamentoController@delete',
    #endregion    

    #region Funil
    '/funil' => 'FunilController@index',
    #endregion
    #region CRM
    '/crm' => 'CrmController@index',
    '/crm/kanbam' => 'CrmController@kanbam',
    '/crm/saveKanbam' => 'CrmController@saveKanbam',
    '/crm/saveEtapa' => 'CrmController@saveEtapa',
    '/crm/list' => 'CrmController@list',
    '/crm/save' => 'CrmController@save',
    '/crm/edit' => 'CrmController@edit',
    '/crm/delete' => 'CrmController@delete',
    '/crm/deleteEtapa' => 'CrmController@deleteEtapa',
    '/crm/history' => 'CrmController@history',
    #endregion

    #region Fornecedores
    '/fornecedores' => 'FornecedoresController@index',
    #endregion

    #region Produtos
    '/produtos' => 'ProdutosController@index',
    '/produtos/listagem' => 'ProdutosController@listagem',
    '/produtos/get' => 'ProdutosController@get',
    '/produtos/save' => 'ProdutosController@save',
    '/produtos/delete' => 'ProdutosController@delte',
    #endregion
    '/estoque' => 'EstoqueController@index',

    #region Vendas
    '/vendas' => 'VendasController@index',
    '/vendas/listagem' => 'VendasController@listagem',
    '/vendas/get' => 'VendasController@get',
    '/vendas/save' => 'VendasController@save',
    '/vendas/delete' => 'VendasController@delete',
    #endregion

    '/configuracoes' => 'ConfigController@index',

    #region Permissões
    '/permissoes' => 'PermissoesController@index',
    '/permissoes/listagem' => 'PermissoesController@listagem',
    '/permissoes/get' => 'PermissoesController@get',
    '/permissoes/save' => 'PermissoesController@save',
    '/permissoes/delete' => 'PermissoesController@delete',
    #endregion

    #region Minha conta
    '/conta' => 'ContaController@index',
    '/conta/listagem' => 'ContaController@listagem',
    '/conta/get' => 'ContaController@get',
    '/conta/save' => 'ContaController@save',
    '/conta/delete' => 'ContaController@delete',
    #endregion








    
    '/instancia/{instancia}/receive' => 'InstanciaWppController@receiveWebhook'


    
    // Adicione outras rotas conforme necessário
];

if (!isset($rotas['/'])) {
    $rotas['/'] = 'LoginController@index';
}

return $rotas;
