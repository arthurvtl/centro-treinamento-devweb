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

    // Alunos (HU01): recepcionista e proprietário; exclusão definitiva só do proprietário
    $routes->group('alunos', static function (RouteCollection $routes): void {
        $routes->get('', 'Alunos::index', ['as' => 'alunos']);
        $routes->get('novo', 'Alunos::novo', ['as' => 'alunos_novo']);
        $routes->post('criar', 'Alunos::criar', ['as' => 'alunos_criar']);
        $routes->get('(:num)', 'Alunos::ver/$1', ['as' => 'alunos_ver']);
        $routes->get('(:num)/editar', 'Alunos::editar/$1', ['as' => 'alunos_editar']);
        $routes->post('(:num)/atualizar', 'Alunos::atualizar/$1', ['as' => 'alunos_atualizar']);
        $routes->post('(:num)/inativar', 'Alunos::inativar/$1', ['as' => 'alunos_inativar']);
        $routes->post('(:num)/reativar', 'Alunos::reativar/$1', ['as' => 'alunos_reativar']);
        $routes->post('(:num)/excluir', 'Alunos::excluir/$1', ['as' => 'alunos_excluir', 'filter' => 'perfil:proprietario']);
    });

    // Modalidades e professores (HU02): somente o proprietário
    $routes->group('modalidades', ['filter' => 'perfil:proprietario'], static function (RouteCollection $routes): void {
        $routes->get('', 'Modalidades::index', ['as' => 'modalidades']);
        $routes->get('novo', 'Modalidades::novo', ['as' => 'modalidades_novo']);
        $routes->post('criar', 'Modalidades::criar', ['as' => 'modalidades_criar']);
        $routes->get('(:num)/editar', 'Modalidades::editar/$1', ['as' => 'modalidades_editar']);
        $routes->post('(:num)/atualizar', 'Modalidades::atualizar/$1', ['as' => 'modalidades_atualizar']);
        $routes->post('(:num)/excluir', 'Modalidades::excluir/$1', ['as' => 'modalidades_excluir']);
    });

    $routes->group('professores', ['filter' => 'perfil:proprietario'], static function (RouteCollection $routes): void {
        $routes->get('', 'Professores::index', ['as' => 'professores']);
        $routes->get('novo', 'Professores::novo', ['as' => 'professores_novo']);
        $routes->post('criar', 'Professores::criar', ['as' => 'professores_criar']);
        $routes->get('(:num)/editar', 'Professores::editar/$1', ['as' => 'professores_editar']);
        $routes->post('(:num)/atualizar', 'Professores::atualizar/$1', ['as' => 'professores_atualizar']);
        $routes->post('(:num)/inativar', 'Professores::inativar/$1', ['as' => 'professores_inativar']);
        $routes->post('(:num)/reativar', 'Professores::reativar/$1', ['as' => 'professores_reativar']);
        $routes->post('(:num)/excluir', 'Professores::excluir/$1', ['as' => 'professores_excluir']);
    });
});
