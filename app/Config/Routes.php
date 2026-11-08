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

    // Turmas (HU03): consulta para os dois perfis; alterações só do proprietário (RN05)
    $routes->group('turmas', static function (RouteCollection $routes): void {
        $routes->get('', 'Turmas::index', ['as' => 'turmas']);
        $routes->get('(:num)', 'Turmas::ver/$1', ['as' => 'turmas_ver']);
        $routes->group('', ['filter' => 'perfil:proprietario'], static function (RouteCollection $routes): void {
            $routes->get('novo', 'Turmas::novo', ['as' => 'turmas_novo']);
            $routes->post('criar', 'Turmas::criar', ['as' => 'turmas_criar']);
            $routes->get('(:num)/editar', 'Turmas::editar/$1', ['as' => 'turmas_editar']);
            $routes->post('(:num)/atualizar', 'Turmas::atualizar/$1', ['as' => 'turmas_atualizar']);
            $routes->post('(:num)/suspender', 'Turmas::suspender/$1', ['as' => 'turmas_suspender']);
            $routes->post('(:num)/reativar', 'Turmas::reativar/$1', ['as' => 'turmas_reativar']);
            $routes->post('(:num)/encerrar', 'Turmas::encerrar/$1', ['as' => 'turmas_encerrar']);
            $routes->post('(:num)/excluir', 'Turmas::excluir/$1', ['as' => 'turmas_excluir']);
        });
    });

    // Matrículas (HU03): recepcionista e proprietário
    $routes->group('matriculas', static function (RouteCollection $routes): void {
        $routes->get('', 'Matriculas::index', ['as' => 'matriculas']);
        $routes->get('nova', 'Matriculas::nova', ['as' => 'matriculas_nova']);
        $routes->post('criar', 'Matriculas::criar', ['as' => 'matriculas_criar']);
        $routes->get('(:num)/trocar', 'Matriculas::trocar/$1', ['as' => 'matriculas_trocar']);
        $routes->post('(:num)/efetivar-troca', 'Matriculas::efetivarTroca/$1', ['as' => 'matriculas_efetivar_troca']);
        $routes->post('(:num)/cancelar', 'Matriculas::cancelar/$1', ['as' => 'matriculas_cancelar']);
        $routes->get('(:num)/certificado', 'Matriculas::certificado/$1', ['as' => 'matriculas_certificado']);
    });

    // Frequência (HU04): recepcionista e proprietário
    $routes->group('frequencias', static function (RouteCollection $routes): void {
        $routes->get('', 'Frequencias::index', ['as' => 'frequencias']);
        $routes->post('registrar', 'Frequencias::registrar', ['as' => 'frequencias_registrar']);
        $routes->get('turma/(:num)/baixa-frequencia', 'Frequencias::baixaFrequencia/$1', ['as' => 'frequencias_baixa']);
    });

    // Planos e financeiro (HU05): somente o proprietário (RN05)
    $routes->group('planos', ['filter' => 'perfil:proprietario'], static function (RouteCollection $routes): void {
        $routes->get('', 'Planos::index', ['as' => 'planos']);
        $routes->get('novo', 'Planos::novo', ['as' => 'planos_novo']);
        $routes->post('criar', 'Planos::criar', ['as' => 'planos_criar']);
        $routes->get('(:num)/editar', 'Planos::editar/$1', ['as' => 'planos_editar']);
        $routes->post('(:num)/atualizar', 'Planos::atualizar/$1', ['as' => 'planos_atualizar']);
        $routes->post('(:num)/inativar', 'Planos::inativar/$1', ['as' => 'planos_inativar']);
        $routes->post('(:num)/reativar', 'Planos::reativar/$1', ['as' => 'planos_reativar']);
        $routes->post('(:num)/excluir', 'Planos::excluir/$1', ['as' => 'planos_excluir']);
    });

    $routes->group('mensalidades', ['filter' => 'perfil:proprietario'], static function (RouteCollection $routes): void {
        $routes->get('', 'Mensalidades::index', ['as' => 'mensalidades']);
        $routes->get('inadimplentes', 'Mensalidades::inadimplentes', ['as' => 'mensalidades_inadimplentes']);
        $routes->get('aluno/(:num)', 'Mensalidades::aluno/$1', ['as' => 'mensalidades_aluno']);
        $routes->post('aluno/(:num)/plano', 'Mensalidades::vincularPlano/$1', ['as' => 'mensalidades_vincular_plano']);
        $routes->post('aluno/(:num)/criar', 'Mensalidades::criar/$1', ['as' => 'mensalidades_criar']);
        $routes->post('(:num)/quitar', 'Mensalidades::quitar/$1', ['as' => 'mensalidades_quitar']);
    });

    // Relatórios (HU06): operacionais para os dois perfis; financeiro só do proprietário (RN05)
    $routes->group('relatorios', static function (RouteCollection $routes): void {
        $routes->get('', 'Relatorios::index', ['as' => 'relatorios']);
        $routes->get('financeiro', 'Relatorios::financeiro', ['as' => 'relatorios_financeiro', 'filter' => 'perfil:proprietario']);
    });
});
