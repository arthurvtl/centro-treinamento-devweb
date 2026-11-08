<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Convenção: GET para leitura/formulários; POST para criar, alterar, excluir ou mudar situação (RNF07).
 * Cada módulo tem seu grupo e rotas nomeadas (`as`), para as views usarem url_to().
 */

// Acesso (público)
$routes->get('/', static fn () => redirect()->to(site_url('painel')));
$routes->get('login', 'Acesso::formularioLogin', ['as' => 'login']);
$routes->post('login', 'Acesso::entrar', ['as' => 'login_entrar']);

// Área autenticada (RF20)
$routes->group('', ['filter' => 'autenticado'], static function (RouteCollection $routes): void {
    $routes->get('painel', 'Painel::index', ['as' => 'painel']);
    $routes->post('sair', 'Acesso::sair', ['as' => 'sair']);
});
